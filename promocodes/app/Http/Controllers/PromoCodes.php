<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\PromoCodes as ModelsPromoCodes;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class PromoCodes extends Controller
{
    //
    public function createPromoCodes(Request $request) {
        $input = $request->all();
        $validated = $this->validatePromoCode($input);
        if ($validated->passes()) {
            //create the venue
            try {
                $venue = Venue::create([
                    'name' => isset($input['name']) ? $input['name'] : null,
                    'latitude' => isset($input['latitude']) ? $input['latitude'] : null,
                    'longitude' => isset($input['longitude']) ? $input['longitude'] : null,
                ]);
                $venue->save();
            } catch (Exception $e) {
                $error = [
                    'status' => 'Error',
                    'status_code' => 400,
                    'message' => $e->getMessage()
                ];
                return response()->json($error, 400);
            }
            //generate promocode
            $promo_code =  bin2hex(random_bytes(4));
            try {
                $promo = ModelsPromoCodes::create(
                    [
                    'promocode' => $promo_code,
                    'no_rides' => isset($input['no_of_rides']) ? $input['no_of_rides'] : null,
                    'expiry_date' => isset($input['expiry']) ? Carbon::parse($input['expiry']) : null,
                    'status' => isset($input['status']) ? $input['status'] : null,
                    'venue_id' => $venue->id,
                    'acceptable_radius' => isset($input['accepted_radius']) ? $input['accepted_radius'] : null,
                    // 'name' => isset($input['name']) ? $input['name'] : null,
                    // 'name' => isset($input['name']) ? $input['name'] : null,
                ]
            );
            $promo->save();

                return response()->json($promo, 201);
            } catch (Exception $e) {
                $error = [
                    'status' => 'Error',
                    'status_code' => 400,
                    'message' => $e->getMessage()
                ];
                return response()->json($error, 400);
            }
            return response()->json($promo, 201);
        } else {
            $error['status'] = 'Error';
            $error['status_code'] = 400;
            $error['message'] = 'The following fields are required.';
            $error['errors'] = $validated->messages();
            $response = $error;
            return response()->json($response, 400);
        }
    }

    public function validatePromoCode($data) {
        return Validator::make($data, [
            'accepted_radius' => ['required', 'numeric'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'expiry' => ['required', 'date_format:Y/m/d'],
        ]);
    }
}
