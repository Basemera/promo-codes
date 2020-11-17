<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\PromoCodes;
use App\Models\Venue;
use Carbon\Carbon;


class PromoCodeValidityTest extends TestCase
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


    public function testMissingFieldsInBody()
    {
        $data = [];
        $response = $this->json('POST', 'http://127.0.0.1:8000/api/promocodes/valid', $data);
        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'Error',
            'status_code' => 400,
            'message' => 'The following fields are required.',
            'errors' => [
                "origin.latitude" => [
                    "Please specify the latitude for the destination"
                ],
                "promo_code" => [
                    "The promo code field is required."
                ],
                "origin.longitude" => [
                    "Please specify the longitude for the destination"
                ], "destination.latitude" => [
                    "The destination.latitude field is required."
                ], "destination.longitude" => [
                    "The destination.longitude field is required."
                ]
            ]
        ]);
    }


    public function testWrongInputTypeInBody()
    {
        $data = [
            "promo_code" => "78601744w",
            "origin" => [
                "latitude" => "string",
                "longitude" => "string"
            ],
            "destination" => [
                "latitude" => "string",
                "longitude" => "string"
            ],
        ];
        $response = $this->json('POST', 'http://127.0.0.1:8000/api/promocodes/valid', $data);
        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'Error',
            'status_code' => 400,
            'message' => 'The following fields are required.',
            'errors' => [
                "origin.latitude" => [
                    "The latitude for the origin must be numeric"
                ],
                "promo_code" => [
                    "Promo code must exist in the database"
                ],
                "origin.longitude" => [
                    "The longitude for the origin must be numeric"
                ], "destination.latitude" => [
                    "The latitude for the destination must be numeric"
                ], "destination.longitude" => [
                    "The longitude for the destination must be numeric"
                ]
            ]
        ]);
    }

    public function testFailWhenPromoCodeInvalid()
    {
        $this->createPromos();
        $data = [
            "promo_code" => "78601744",
            "origin" => [
                "latitude" => "0.362890",
                "longitude" => "32.479194"
            ],
            "destination" => [
                "latitude" => "0.312379",
                "longitude" => "32.526144"
            ],
        ];
        $response = $this->json('POST', 'http://127.0.0.1:8000/api/promocodes/valid', $data);
        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'Error',
            'status_code' => 400,
            'message' => 'Promocode not valid'
        ]);
    }

    public function testFailWhenVenueNotPartOfJourney() {
        $this->createPromos();
        $data = [
            "promo_code" => "22ae4bcf",
            "origin" => [
                "latitude" => "0.362890",
                "longitude" => "32.479194"
            ],
            "destination" => [
                "latitude" => "-1.285293",
                "longitude" => "29.828145"
            ],
        ];
        $response = $this->json('POST', 'http://127.0.0.1:8000/api/promocodes/valid', $data);
        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'Error',
            'status_code' => 400,
            'message' => 'Route should either begin or end at venue'
        ]);
    }


    public function testFailWhenRouteOutsideAcceptableRange() {
        $this->createPromos();
        $data = [
            "promo_code" => "22ae4bcf",
            "origin" => [
                'latitude' => '0.312379',
                'longitude' => '32.526144'
            ],
            "destination" => [
                "latitude" => "-1.285293",
                "longitude" => "29.828145"
            ],
        ];
        $response = $this->json('POST', 'http://127.0.0.1:8000/api/promocodes/valid', $data);
        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'Error',
            'status_code' => 400,
            'message' => 'Route outside acceptable range'
        ]);
    }


    public function testReturnDetailsValidPromoCode() {
        $this->createPromos();
        $data = [
            "promo_code" => "22ae4bcf",
            "origin" => [
                'latitude' => '0.312379',
                'longitude' => '32.526144'
            ],
            "destination" => [
                "latitude" => "0.362890",
                "longitude" => "32.479194"
            ],
        ];
        $response = $this->json('POST', 'http://127.0.0.1:8000/api/promocodes/valid', $data);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'promo_code' => [
                'promocode',
            'no_rides',
            'expiry_date',
            'status',
            'venue_id',
            'acceptable_radius',
            'updated_at',
            'created_at',
            'id'
            ],
            'polyline' => [
                'rawPoints',
                'levels',
                'points',
                'numLevels',
                'zoomFactor'
            ]
        ]);
    }

    public function createPromos()
    {
        $venue = Venue::create([
            'name' => 'La Grand',
            'latitude' => '0.312379',
            'longitude' => '32.526144'
        ]);
        $promo_code =  bin2hex(random_bytes(4));
        $date = Carbon::now()->addDays(30);
        $promo = PromoCodes::create([
            'promocode' => "78601744",
            'no_rides' => 4,
            'expiry_date' => $date,
            'status' => 2,
            'venue_id' => $venue->id,
            'acceptable_radius' => 10
        ]);

        $promo_code =  bin2hex(random_bytes(4));

        $promo2 = PromoCodes::create([
            'promocode' => "22ae4bcf",
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
}
