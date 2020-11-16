<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PromoCodesTest extends TestCase
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


    public function testErrorWhenWrongInputToCreatePromo()
    {
        $data = [];
        $response = $this->json('POST', '/api/promocodes/create', $data);
        $response
            ->assertStatus(400)
            ->assertJson([
                'status' => 'Error',
                'status_code' => 400,
                'message' => 'The following fields are required.',
                'errors' => [
                    "accepted_radius" => [
                        "The accepted radius field is required."
                    ],
                    "latitude" => [
                        "The latitude field is required."
                    ],
                    "longitude" => [
                        "The longitude field is required."
                    ],
                    "expiry" => [
                        "The expiry field is required."
                    ]
                ]
            ]);
    }

    public function testErrorWhenWrongInputTypes()
    {
        $data = [
            'accepted_radius' => 'string',
            'latitude' => 'string',
            'longitude' => 'string'
        ];
        $response = $this->json('POST', '/api/promocodes/create', $data);
        $response
            ->assertStatus(400)
            ->assertJson([
                'status' => 'Error',
                'status_code' => 400,
                'message' => 'The following fields are required.',
                'errors' => [
                    "accepted_radius" => [
                        "The accepted radius must be a number."
                    ],
                    "latitude" => [
                        "The latitude must be a number."
                    ],
                    "longitude" => [
                        "The longitude must be a number."
                    ]
                ]
            ]);
    }

    public function testCreatePromoCode()
    {
        $data = [
            "latitude" => "0.312379",
            "longitude" => "32.526144",
            "accepted_radius" => "10",
            "expiry" => "2020/12/29",
            "no_of_rides" => 4,
            "name" => "La Grand",
            "status" => 1
        ];
        $response = $this->json('POST', '/api/promocodes/create', $data);
        $response->assertStatus(201);
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

    public function testFailureIfDateInIncorrectFormat() {
        $data = [
            "latitude" => "0.312379",
            "longitude" => "32.526144",
            "accepted_radius" => "10",
            "expiry" => "29/12/2020",
            "no_of_rides" => 4,
            "name" => "La Grand",
            "status" => 1
        ];

        $response = $this->json('POST', '/api/promocodes/create', $data);
        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'Error',
            'status_code' => 400,
            'message' => 'The following fields are required.',
            'errors' => [
                "expiry" => [
                    "The expiry does not match the format Y/m/d."
                    ]
            ]
        ]);
    }
}
