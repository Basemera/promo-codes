<?php

namespace Tests\Feature;

use App\Models\PromoCodes;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Carbon\Carbon;

class PromoCodesActivationTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test error is returned when fields are missing in request body
     *
     * @return void
     */
    public function testMissingFieldsInBodyForActivation()
    {
        $data = [];
        $response = $this->json('POST', 'http://127.0.0.1:8000/api/promocodes/activate');
        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'Error',
            'status_code' => 400,
            'message' => 'The following fields are required.',
            'errors' => [
                "status" => [
                    "The status field is required."
                  ],
                  "promo_code" => [
                    "The promo code field is required."
                  ]
            ]
        ]);
    }

    
    /**
     * Test error is returned when inputs are of the wrong type
     *
     * @return void
     */
    public function testWrongInputTypeForActivation()
    {
        $data = [
            'status' => 3,
            'promo_code' => 'string'
        ];
        $response = $this->json('POST', 'http://127.0.0.1:8000/api/promocodes/activate', $data);
        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'Error',
            'status_code' => 400,
            'message' => 'The following fields are required.',
            'errors' => [
                "status" => [
                    "The selected status is invalid."
                ],
                  "promo_code" => [
                    "The selected promo code is invalid."
                    ]
            ]
        ]);
    }

    
    /**
     * Test successfully activate a promo code
     *
     * @return void
     */
    public function testActivatePromoCode() {
        //create venue
        $venue = Venue::create([
            'name' => 'La Grand',
            'latitude' => '0.312379',
            'longitude' => '32.526144'
        ]);
        $promo_code =  bin2hex(random_bytes(4));
        $date = Carbon::now()->addDays(30);
        $promo = PromoCodes::create([
            'promocode' => $promo_code,
            'no_rides' => 4,
            'expiry_date' => $date,
            'status' => 2,
            'venue_id' => $venue->id,
            'acceptable_radius' => 10
        ]);
        $data = [
            'status' => 1,
            'promo_code' => $promo_code
        ];
        $response = $this->json('POST', 'http://127.0.0.1:8000/api/promocodes/activate', $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'promocode',
            'no_rides',
            'expiry_date',
            'status',
            'venue_id',
            'acceptable_radius',
            'updated_at',
            'created_at',
            'id'
        ]);
    }

    /**
     * Test successfully activate a promo code
     *
     * @return void
     */
    public function testActivateSuccessfulWhenPromoCodeAlreadyActivated() {
        //create venue
        $venue = Venue::create([
            'name' => 'La Grand',
            'latitude' => '0.312379',
            'longitude' => '32.526144'
        ]);
        $promo_code =  bin2hex(random_bytes(4));
        $date = Carbon::now()->addDays(30);
        $promo = PromoCodes::create([
            'promocode' => $promo_code,
            'no_rides' => 4,
            'expiry_date' => $date,
            'status' => 1,
            'venue_id' => $venue->id,
            'acceptable_radius' => 10
        ]);
        $data = [
            'status' => 1,
            'promo_code' => $promo_code
        ];
        $response = $this->json('POST', 'http://127.0.0.1:8000/api/promocodes/activate', $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'promocode',
            'no_rides',
            'expiry_date',
            'status',
            'venue_id',
            'acceptable_radius',
            'updated_at',
            'created_at',
            'id'
        ]);
    }

    /**
     * Test error is returned when promo code to be activated has expired
     *
     * @return void
     */
    public function testActivateFailsWhenPromoCodeExpired() {
        //create venue
        $venue = Venue::create([
            'name' => 'La Grand',
            'latitude' => '0.312379',
            'longitude' => '32.526144'
        ]);
        $promo_code =  bin2hex(random_bytes(4));
        $date = Carbon::now()->subDays(3);
        $promo = PromoCodes::create([
            'promocode' => $promo_code,
            'no_rides' => 4,
            'expiry_date' => $date,
            'status' => 2,
            'venue_id' => $venue->id,
            'acceptable_radius' => 10
        ]);
        $data = [
            'status' => 1,
            'promo_code' => $promo_code
        ];
        $response = $this->json('POST', 'http://127.0.0.1:8000/api/promocodes/activate', $data);
        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'Error',
            'status_code' => 400,
            'message' => 'Promo code already expired',
        ]);
    }
}
