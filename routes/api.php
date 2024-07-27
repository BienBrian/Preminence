<?php
use App\Http\Controllers\APIs\AuthController;
use App\Http\Controllers\APIs\Dashboard\Auctions\AuctionAPIController;
use App\Http\Controllers\APIs\Dashboard\HomeAPIController;
use App\Http\Controllers\APIs\Dashboard\Shares\ShareAPIController;
use App\Http\Controllers\APIs\MpesaAPIController;
use Illuminate\Support\Facades\Route;


Route::post('/access/token', [MpesaAPIController::class, 'generateAccessToken']);
Route::post('/stk/push',  [MpesaAPIController::class, 'customerMpesaSTKPush']);
Route::post('stk/confirmation',  [MpesaAPIController::class, 'stkResponse']);
Route::post('/validation',  [MpesaAPIController::class, 'mpesaValidation']);
Route::post('/transaction/confirmation',  [MpesaAPIController::class, 'mpesaConfirmation']);
Route::post('/register/url',  [MpesaAPIController::class, 'mpesaRegisterUrls']);


Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::group(['prefix' => 'dashboard', 'middleware' => 'auth:sanctum'], function () {
        Route::get('home', [HomeAPIController::class, 'getDashboard']);

        //shares
        Route::get('shares', [ShareAPIController::class, 'getShares']);
        //auctions
        Route::get('auctions', [AuctionAPIController::class, 'getAuctions']);

        Route::get('user', [AuthController::class, 'user']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});
