<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

use App\Models\PromoCodes as ModelsPromoCodes;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class PromoCodes extends Controller
{
    //
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
                        'acceptable_radius' => isset($input['accepted_radius']) ? $input['accepted_radius'] : null,                    ]
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

    public function validatePromoCode($data)
    {
        return Validator::make($data, [
            'accepted_radius' => ['required', 'numeric'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'expiry' => ['required', 'date_format:Y/m/d'],
        ]);
    }

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

    public function validateDeactivatePromoCode($data)
    {
        return Validator::make($data, [
            'status' => ['required', 'integer', Rule::in([1, 2])],
            'promo_code' => ['required', 'exists:promo_codes,promocode']
        ]);
    }
}
