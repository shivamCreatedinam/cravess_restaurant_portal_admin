<?php

use App\Http\Controllers\Restaurant\ProductController;
use App\Http\Controllers\Restaurant\RestaurantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Users API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['prefix' => 'v1'], function () {

    Route::post("get-items", [ProductController::class, "allProducts"]);
    Route::post("get-stores", [RestaurantController::class, "getAllStores"]);
    Route::group(['middleware' => ['jwt']], function () {
    });
});
