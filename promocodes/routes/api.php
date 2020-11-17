<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => []],function() {
    Route::post('promocodes/create', 'App\Http\Controllers\PromoCodes@createPromoCodes');
    Route::post('promocodes/deactivate', 'App\Http\Controllers\PromoCodes@deactivatePromoCode');
    Route::post('promocodes/activate', 'App\Http\Controllers\PromoCodes@deactivatePromoCode');
    Route::get('promocodes/promocodes', 'App\Http\Controllers\PromoCodes@getPromoCodes');
    Route::post('promocodes/valid', 'App\Http\Controllers\PromoCodes@getPromoCodevalidity');
}
);
