<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\AccountController;

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

// fetch user's info api
Route::middleware('auth:sanctum')->get('/user', function(Request $request) {
    return $request->user();
});


// register and login api
Route::post('/register', [AuthenticationController::class, 'register']);
Route::post('/login', [AuthenticationController::class, 'login']);


// account's api
Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'account/'], function() {
    Route::get('show', [AccountController::class, 'show']);
    Route::post('register', [AccountController::class, 'store']);
    Route::put('update', [AccountController::class, 'update']);
    Route::post('transfer', [AccountController::class, 'transfer']);
    Route::get('transactions', [AccountController::class, 'transactions']);
});