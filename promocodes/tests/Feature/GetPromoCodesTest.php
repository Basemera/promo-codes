<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\PromoCodes;
use App\Models\Venue;
use Carbon\Carbon;


class GetPromoCodesTest extends TestCase
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

    public function createPromos() {
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

        $promo_code =  bin2hex(random_bytes(4));

        $promo2 = PromoCodes::create([
            'promocode' => $promo_code,
            'no_rides' => 4,
            'expiry_date' => $date,
            'status' => 1,
            'venue_id' => $venue->id,
            'acceptable_radius' => 10
        ]);

        $date1 = Carbon::now()->subDays(30);
        $promo_code =  bin2hex(random_bytes(4));

        $promo = PromoCodes::create([
            'promocode' => $promo_code,
            'no_rides' => 4,
            'expiry_date' => $date1,
            'status' => 1,
            'venue_id' => $venue->id,
            'acceptable_radius' => 10
        ]);
    }

    public function testGetActivePromoCodes() {
        $this->createPromos();
        $response = $this->json('GET', 'http://127.0.0.1:8000/api/promocodes/promocodes?q=Active');
        $response->assertStatus(200);
        $response->assertSee($response['count'], 1);
    }


    public function testGetAllPromoCodes() {
        $this->createPromos();
        $response = $this->json('GET', 'http://127.0.0.1:8000/api/promocodes/promocodes');
        $response->assertStatus(200);
        $response->assertSee($response['count'], 3);
    }
}
