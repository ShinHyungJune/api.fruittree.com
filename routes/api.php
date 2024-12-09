<?php

use App\Enums\ProductCategory;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductInquiryController;
use App\Http\Controllers\Api\ProductReviewController;
use App\Http\Controllers\Api\VerifyNumberController;
use Illuminate\Support\Facades\Route;

/*Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});*/

/*Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});*/

/*Route::get('test', function(){
    //dd(ProductCategory::values());
    //dd(implode('|', ProductCategory::values()));
    dd(ProductCategory::from('best'));
});*/


//사용자인증
Route::group(['controller' => AuthController::class], function () {
    Route::post('login', 'login');
    Route::post('register', 'store');

    //번호인증
    Route::group(['controller' => VerifyNumberController::class], function () {
        Route::post('verify-numbers', 'store');
        Route::put('verify-numbers', 'update');
    });

    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('logout', 'logout');
        Route::get('profile', 'profile');
        //Route::get('refresh', 'refresh');
    });
});


//상품보기
Route::group(['prefix' => 'products'], function () {
    Route::group(['controller' => ProductController::class], function () {
        Route::get('md_packages', 'mdPackages');
        Route::get('{category?}', 'index')->where('category', implode('|', ProductCategory::values()));
        Route::get('{product}', 'show');
    });

    //상품리뷰
    Route::group(['prefix' => '{id}/reviews', 'controller' => ProductReviewController::class], function () {
        Route::get('', 'index');
        Route::group(['middleware' => ['auth:api']], function () {
            Route::post('', 'store');
        });
    });

    //상품문의
    Route::group(['prefix' => '{id}/inquiries', 'controller' => ProductInquiryController::class], function () {
        Route::get('', 'index');
        Route::group(['middleware' => ['auth:api']], function () {
            Route::post('', 'store');
        });
    });
});

/*Route::group(['prefix' => 'gifts', 'controller' => GiftController::class], function () {
});*/

Route::middleware('auth:api')->group(function () {
    /*Route::get('/protected-route', function () {
        return response()->json(['message' => 'This is a protected route']);
    });*/
    require __DIR__.'/admin.php';
});

require __DIR__.'/post.php';
