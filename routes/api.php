<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductDetailController;
use App\Http\Controllers\StatisticsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/////////////////////////// For Test
Route::get('/test-online', function () {
    return 1;
});

/////////////////////////// Auth
// User
Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
// Admin
Route::post('/admin',[AuthController::class,'addAdmin']);
Route::post('/admin-login',[AuthController::class,'adminLogin']);
Route::post('/forget-password',[AuthController::class,'forgetPassword']);
Route::post('/reset-password',[AuthController::class,'resetPassword']);

/////////////////////////// Product
Route::get('/product/{id}',[ProductController::class,'getProduct']);
Route::get('/new-products',[ProductController::class,'getNewProducts']);
Route::get('/freq-products',[ProductController::class,'getFreqProducts']);
Route::get('/products/{id}',[ProductController::class,'getProductsOfCategory']);
Route::post('/search-products/{id}',[ProductController::class,'searchProductsOfCategory']);

/////////////////////////// Category
Route::get('/categories',[CategoryController::class,'getCategories']);
Route::get('/subcategories/{id}',[CategoryController::class,'getSubcategoriesOfCategory']);

Route::group(['middleware' => 'auth:api'], function(){
    
    /////////////////////////// Auth
    Route::post('/change-password',[AuthController::class,'changePassword']);
    Route::get('/logout',[AuthController::class,'logout']);

    /////////////////////////// Category
    Route::post('/search-categories',[CategoryController::class,'searchCategories']);
    Route::get('/subcategories',[CategoryController::class,'getSubcategories']);
    Route::post('/search-subcategories',[CategoryController::class,'searchSubcategories']);
    Route::get('/subsubcategories',[CategoryController::class,'getSubsubcategories']);
    Route::post('/search-subsubcategories',[CategoryController::class,'searchSubsubcategories']);
    Route::post('/category',[CategoryController::class,'addCategory']);
    Route::put('/category/{id}',[CategoryController::class,'updateCategory']);
    Route::delete('/category/{id}',[CategoryController::class,'deleteCategory']);

    /////////////////////////// Product
    Route::get('/products',[ProductController::class,'getProducts']);
    Route::post('/search-products',[ProductController::class,'searchProducts']);
    Route::post('/product',[ProductController::class,'addProduct']);
    Route::put('/product/{id}',[ProductController::class,'updateProduct']);
    Route::delete('/product/{id}',[ProductController::class,'deleteProduct']);

    /////////////////////////// Product Detail
    Route::post('/product-detail',[ProductDetailController::class,'addProductDetail']);
    Route::put('/product-detail/{id}',[ProductDetailController::class,'updateProductDetail']);
    Route::delete('/product-detail/{id}',[ProductDetailController::class,'deleteProductDetail']);

    /////////////////////////// Favorite
    Route::get('/favorites',[FavoriteController::class,'getFavorites']);
    Route::post('/search-favorites',[FavoriteController::class,'searchFavorites']);
    Route::post('/favorite/{id}',[FavoriteController::class,'addFavorite']);
    Route::delete('/favorite/{id}',[FavoriteController::class,'deleteFavorite']);

    /////////////////////////// Cart
    Route::get('/carts',[CartController::class,'getCarts']);
    Route::post('/search-carts',[CartController::class,'searchCarts']);
    Route::post('/cart/{id}',[CartController::class,'addCart']);
    Route::put('/cart/{id}',[CartController::class,'updateCart']);
    Route::delete('/cart/{id}',[CartController::class,'deleteCart']);

    /////////////////////////// Order
    Route::get('/orders',[OrderController::class,'getOrders']);
    Route::get('/order-products/{id}',[OrderController::class,'getProductsOfOrder']);
    Route::post('/order',[OrderController::class,'addOrder']);
    Route::put('/order/{id}',[OrderController::class,'changeOrderState']);

    /////////////////////////// Statistics
    Route::get('/statistics',[StatisticsController::class,'getAdminStatistics']);
});


