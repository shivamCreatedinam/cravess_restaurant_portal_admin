<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\ProductChildCategoryController;
use App\Http\Controllers\Admin\ProductController;
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


Route::group(['middleware' => 'auth:web'], function () {

    Route::get('/dashboard', [HomeController::class, "dashboard"])->name("dashboard");

    Route::group(['prefix' => 'child'], function () {
        Route::get('/category-list', [ProductChildCategoryController::class, "index"])->name("child_cat_list");
        Route::get('/add-category', [ProductChildCategoryController::class, "createPage"])->name("child_cat_add");
        Route::post('/get-sub-category', [ProductChildCategoryController::class, "getSubCategory"])->name("get_sub_category");
        Route::post('/get-child-category', [ProductChildCategoryController::class, "getChildCategory"])->name("get_child_category");
        Route::post('/store-child-category', [ProductChildCategoryController::class, "storeChildCategory"])->name("store_child_category");
        Route::get('/edit-child-category/{id}', [ProductChildCategoryController::class, "editChildCategory"])->name("edit_child_category");
        Route::post('/update-child-category', [ProductChildCategoryController::class, "updateChildCategory"])->name("update_child_category");
        Route::get('/delete-child-category/{id}', [ProductChildCategoryController::class, "deleteChildCategory"])->name("delete_child_category");
    });

    Route::group(['prefix' => 'product'], function () {
        Route::get('/list', [ProductController::class, "index"])->name("product_list");
        Route::get('/add', [ProductController::class, "addPage"])->name("product_add_page");
        Route::post('/store', [ProductController::class, "storeItem"])->name("store_new_item");
        Route::get('/image-upload/{product_id}', [ProductController::class, "productImageUpload"])->name("product_image_upload");
        Route::post('/image-upload', [ProductController::class, "productImageUploadPost"])->name("product_image_upload_post");
    });

    Route::get("logout", [AuthController::class, "logout"])->name("admin_logout");
});
