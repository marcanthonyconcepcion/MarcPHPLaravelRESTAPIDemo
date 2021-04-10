<?php
/*
 * Copyright (c) 2021.
 * Marc Concepcion
 * marcanthonyconcepcion@gmail.com
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubscriberController;

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
Route::get('subscribers', [SubscriberController::class,'index']);
Route::get('subscribers/{subscriber}', [SubscriberController::class,'show']);
Route::post('subscribers', [SubscriberController::class,'store']);
Route::put('subscribers/{subscriber}', [SubscriberController::class,'update']);
Route::patch('subscribers/{subscriber}', [SubscriberController::class,'update']);;
Route::delete('subscribers/{subscriber}', [SubscriberController::class,'delete']);
Route::fallback(function () {
    return response()->json(['error' => 'Invalid URL syntax. Please provide acceptable HTTP URL.'], 400);
});
