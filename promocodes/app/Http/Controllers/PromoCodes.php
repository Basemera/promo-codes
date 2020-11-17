<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

use App\Models\PromoCodes as ModelsPromoCodes;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\PolylineEncoder;
class PromoCodes extends Controller
{
    //
    /**
     * Create promo codes
     *
     * @param Request $request
     * @return ModelsPromoCodes
     */
    public function createPromoCodes(Request $request)
    {
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
                        'status' => isset($input['status']) ? $input['status'] : 1,
                        'venue_id' => $venue->id,
                        'acceptable_radius' => isset($input['accepted_radius']) ? $input['accepted_radius'] : null,
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

    /**
     * Validate create promo code input
     *
     * @param array $data
     * @return Validator
     */
    public function validatePromoCode($data)
    {
        return Validator::make($data, [
            'accepted_radius' => ['required', 'numeric'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'expiry' => ['required', 'date_format:Y/m/d'],
        ]);
    }

    /**
     * Deactivate promo codes
     *
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function deactivatePromoCode(Request $request)
    {
        $input = $request->all();
        $validated = $this->validateDeactivatePromoCode($input);
        if ($validated->passes()) {
            //get promocode
            try {
                $promo_code = ModelsPromoCodes::where('promocode', $input['promo_code'])->firstOrFail();
            } catch (Exception $e) {
                $error = [
                    'status' => 'Error',
                    'status_code' => 400,
                    'message' => $e->getMessage()
                ];
                return response()->json($error, 400);
            }
            if ($input['status'] == ModelsPromoCodes::ACTIVE) {
                //activate promo code
                //check if expired
                $expired = $promo_code->checkIfCodeIsExpired();
                if ($expired == true) {
                    $error['status'] = 'Error';
                    $error['status_code'] = 400;
                    $error['message'] = 'Promo code already expired';
                    return response()->json($error, 400);
                }
                $promo_code->status = $input['status'];
                $promo_code->save();
                $promo_code->refresh();
                return response()->json($promo_code, 200);
            } else {
                if ($promo_code->status == ModelsPromoCodes::DEACTIVATED) {
                    $error['status'] = 'Error';
                    $error['status_code'] = 400;
                    $error['message'] = 'Promo code already deactivated';
                    return response()->json($error, 400);
                }
                $promo_code->status = $input['status'];
                $promo_code->save();
                $promo_code->refresh();
                return response()->json($promo_code, 200);
            }
        } else {
            $error['status'] = 'Error';
            $error['status_code'] = 400;
            $error['message'] = 'The following fields are required.';
            $error['errors'] = $validated->messages();
            $response = $error;
            return response()->json($response, 400);
        }
    }

    /**
     * Get promo codes
     *
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function getPromoCodes(Request $request)
    {
        $input = $request->all();
        if (isset($input['q']) && strtolower($input['q']) == 'active') {
            $promos = $this->getActivePromoCodes(true);
            $count = $promos->count();
            $response = [
                'count' => $count,
                'codes' => $promos
            ];
            return response()->json($response, 200);
        }

        $promos = $this->getActivePromoCodes();
        $count = $promos->count();
        $response = [
            'count' => $count,
            'codes' => $promos
        ];
        return response()->json($response, 200);
    }

    /**
     * Get active promo codes
     *
     * @param boolean $active
     * @return void
     */
    protected function getActivePromoCodes($active = false)
    {
        if ($active == true) {
            return ModelsPromoCodes::where([
                ['status', 1],
                ['expiry_date', '>=', Carbon::now()]
            ])
                ->get();
        } else {
            return ModelsPromoCodes::all();
        }
    }

    /**
     * Validate deactivate promo code input
     *
     * @param array $data
     * @return Validator
     */
    public function validateDeactivatePromoCode($data)
    {
        return Validator::make($data, [
            'status' => ['required', 'integer', Rule::in([1, 2])],
            'promo_code' => ['required', 'exists:promo_codes,promocode']
        ]);
    }

    /**
     * Get promo code validity
     *
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function getPromoCodevalidity(Request $request)
    {
        $input = $request->all();
        $validated = $this->validatePromoCodeValidity($input);
        if ($validated->passes()) {
            //get venue for promo code
            try {
                $promo_code = ModelsPromoCodes::where('promocode', $input['promo_code'])
                    ->firstOrFail();
                $venue = Venue::findOrFail($promo_code->venue_id);
            } catch (Exception $e) {
                $error = [
                    'status' => 'Error',
                    'status_code' => 400,
                    'message' => $e->getMessage()
                ];
                return response()->json($error, 400);
            }
            //check if code is valid
            if ($promo_code->checkIfCodeIsValid() == false) {
                $error = [
                    'status' => 'Error',
                    'status_code' => 400,
                    'message' => 'Promocode not valid'
                ];
                return response()->json($error, 400);
            }
            if (
                $input['origin']['latitude'] == $venue->latitude &&
                $input['origin']['longitude'] == $venue->longitude ||
                $input['destination']['latitude'] == $venue->latitude &&
                $input['destination']['longitude'] == $venue->longitude
            ) {
                $data = [
                    'lat1' => $input['origin']['latitude'],
                    'lon1' => $input['origin']['longitude'],
                    'lat2' => $input['destination']['latitude'],
                    'lon2' => $input['destination']['longitude'],
                ];
                $dist = $this->getDistanceBetweenUserDestinationAndVenue($data);
                if ($dist > $promo_code->acceptable_radius) {
                    $error = [
                        'status' => 'Error',
                        'status_code' => 400,
                        'message' => 'Route outside acceptable range'
                    ];
                    return response()->json($error, 400);
                }

                $points = [
                    [
                        $input['destination']['latitude'],
                        $input['destination']['longitude'],
                    ],
                    [
                        $input['origin']['latitude'],
                        $input['origin']['longitude'],
                    ]
                    ];
                $polyline_encorder = new PolylineEncoder();
                $pp = $polyline_encorder->encode($points);
                return response()->json([
                    'promo_code' => $promo_code,
                    'polyline' => $pp
                ], 200);           
            } else {
                $error = [
                    'status' => 'Error',
                    'status_code' => 400,
                    'message' => 'Route should either begin or end at venue'
                ];
                return response()->json($error, 400);
            }
        } else {
            $error['status'] = 'Error';
            $error['status_code'] = 400;
            $error['message'] = 'The following fields are required.';
            $error['errors'] = $validated->messages();
            $response = $error;
            return response()->json($response, 400);
        }
    }

    /**
     * Get distance between destination and origin
     *
     * @param array $data
     * @param string $unit
     * @return integer $miles
     */
    protected function getDistanceBetweenUserDestinationAndVenue($data, $unit='k')
    {
        $lat1 = $data['lat1'];
        $lat2 = $data['lat2'];
        $lon1 = $data['lon1'];
        $lon2 = $data['lon2'];
        
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
          }
          else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);
        
            if ($unit == "K") {
              return ($miles * 1.609344);
            } else if ($unit == "N") {
              return ($miles * 0.8684);
            } else {
              return $miles;
            }
          }
    }

    /**
     * Validate promo code validity input
     *
     * @param array $data
     * @return Validator
     */
    public function validatePromoCodeValidity($data)
    {
        $messages = [
            'origin.latitude.required' => 'Please specify the latitude for the origin',
            'origin.longitude.required' => 'Please specify the longitude for the origin',
            'origin.latitude.required' => 'Please specify the latitude for the destination',
            'origin.longitude.required' => 'Please specify the longitude for the destination',

            'origin.latitude.numeric' => 'The latitude for the origin must be numeric',
            'origin.longitude.numeric' => 'The longitude for the origin must be numeric',
            'destination.latitude.numeric' => 'The latitude for the destination must be numeric',
            'destination.longitude.numeric' => 'The longitude for the destination must be numeric',
            'promo_code.exists' => "Promo code must exist in the database"
        ];
        return Validator::make($data, [
            'promo_code' => ['required', 'exists:promo_codes,promocode'],
            'origin.latitude' => ['required', 'numeric'],
            'origin.longitude' => ['required', 'numeric'],
            'destination.latitude' => ['required', 'numeric'],
            'destination.longitude' => ['required', 'numeric'],
        ], $messages);
    }
}
