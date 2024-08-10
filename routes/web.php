<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Super Admin Panel Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get("/", [AuthController::class, "loginView"])->name("login");
Route::post("login", [AuthController::class, "loginPost"])->name("loginPost");

Route::get("header", [HomeController::class, "printHeaders"]);


Route::group(['prefix' => 'resto','middleware' => 'auth:web'], function () {

    Route::get('/dashboard', [HomeController::class, "dashboard"])->name("dashboard");

    Route::group(['prefix' => 'resto'], function () {
        Route::get('/pending-list', [RestaurantController::class, "pending_list"])->name("resto_pending_list");
        Route::get('/list', [RestaurantController::class, "index"])->name("resto_list");
        Route::get('/resto-view-pending/{user_id}', [RestaurantController::class, "restoViewPending"])->name("resto_view_pending");
        Route::get('/resto-view/{user_id}', [RestaurantController::class, "restoView"])->name("resto_view");
        Route::get('/resto-edit/{user_id}', [RestaurantController::class, "restoEdit"])->name("resto_edit");
        Route::post('/user-update', [UserController::class, "userUpdate"])->name("admin_user_update");
        Route::post('/user-status-update', [UserController::class, "userStatusUpdate"])->name("admin_user_status_update");
        Route::post('/resto-approve', [RestaurantController::class, "restoApprove"])->name("resto_approve");
    });

    Route::get("logout", [AuthController::class, "logout"])->name("admin_logout");
});
