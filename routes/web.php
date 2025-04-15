<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\admin\AdminLoginController;
use App\Http\Controllers\admin\HomeController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\Controller;
use App\Http\Controllers\admin\TempImagesController;
use App\Http\Controllers\admin\SubCategoryController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\ProductSubCategoryController;
use App\Http\Controllers\admin\ProductImageController;
use App\Http\Controllers\admin\ShippingController;
use App\Http\Controllers\admin\DiscountCouponController;
use App\Http\Controllers\admin\OrderController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\admin\PageController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Str;

use Illuminate\Http\Request;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

// Route front

Route::get('/',[FrontController::class,'index'])->name('front.home');
Route::get('/shop/{categorySlug?}/{subCategorySlug?}',[ShopController::class,'index'])->name('front.shop');
Route::get('/product/{slug}',[ShopController::class,'product'])->name('front.product');
Route::get('/cart',[CartController::class,'cart'])->name('front.cart');
Route::post('/add-to-cart',[CartController::class,'addToCart'])->name('front.addToCart');
Route::post('/update-cart',[CartController::class,'updateCart'])->name('front.updateCart');
Route::post('/delete-item-cart',[CartController::class,'deleteItemCart'])->name('front.deleteItemCart');
Route::get('/checkout',[CartController::class,'checkout'])->name('front.checkout');
Route::post('/handleCheckout',[CartController::class,'handleCheckout'])->name('front.handleCheckout');
Route::get('/thanks/{orderId}',[CartController::class,'thankyou'])->name('front.thankyou');
Route::post('/get-order-summery',[CartController::class,'getOrderSummery'])->name('front.getOrderSummery');
Route::post('/apply-discount',[CartController::class,'applyDiscount'])->name('front.applyDiscount');
Route::post('/remove-discount',[CartController::class,'removeDiscount'])->name('front.removeDiscount');
Route::post('/add-to-wishlist',[FrontController::class,'addToWishlist'])->name('front.addToWishlist');


Route::group(['prefix' => 'account'],function(){
    Route::group(['middleware' => 'guest'],function(){
        Route::get('/register',[AuthController::class,'register'])->name('account.register');
        Route::post('/handle-register',[AuthController::class,'handleRegister'])->name('account.handleRegister');
        Route::get('/login',[AuthController::class,'login'])->name('account.login');
        Route::post('/handle-login',[AuthController::class,'authenticate'])->name('account.authenticate');
    });

    Route::group(['middleware' => 'auth'],function(){
        Route::get('/profile',[AuthController::class,'profile'])->name('account.profile');
        Route::post('/profile-info/update',[AuthController::class,'updateProfile'])->name('account.updateProfile');
        Route::post('/profile-address/update',[AuthController::class,'updateAddress'])->name('account.updateAddress');
        Route::get('/my-order',[AuthController::class,'orders'])->name('account.orders');
        Route::get('/my-wishlists',[AuthController::class,'wishlist'])->name('account.wishlist');
        Route::post('/remove-wishlists',[AuthController::class,'removeProductWishlist'])->name('account.removeWishlist');
        Route::get('/order-detail/{orderId}',[AuthController::class,'orderDetail'])->name('account.orderDetail');
        Route::get('/logout',[AuthController::class,'logout'])->name('account.logout');

        

    });
}
);

// Route admin

Route::group(['prefix' => 'admin'],function(){
    Route::group(['middleware' => 'admin.guest'],function(){
        Route::get('/login',[AdminLoginController::class,'index'])->name('admin.login');
        Route::post('/authenticate',[AdminLoginController::class,'authenticate'])->name('admin.authenticate');

    });
    Route::group(['middleware' => 'admin.auth'],function(){
        Route::get('/dashboard',[HomeController::class,'index'])->name('admin.dashboard');
        Route::get('/logout',[HomeController::class,'logout'])->name('admin.logout');

        // Category routes
        Route::get('/categories',[CategoryController::class,'index'])->name('categories.index');
        Route::get('/categories/create',[CategoryController::class,'create'])->name('categories.create');
        Route::post('/categories/store',[CategoryController::class,'store'])->name('categories.store');
        Route::get('/categories/{category}/edit',[CategoryController::class,'edit'])->name('categories.edit');
        Route::put('/categories/{category}',[CategoryController::class,'update'])->name('categories.update');
        Route::delete('/categories/{category}',[CategoryController::class,'destroy'])->name('categories.delete');

        // Sub category routes
        Route::get('/sub-categories',[SubCategoryController::class,'index'])->name('sub-categories.index');
        Route::get('/sub-categories/create',[SubCategoryController::class,'create'])->name('sub-categories.create');
        Route::post('/sub-categories/store',[SubCategoryController::class,'store'])->name('sub-categories.store');
        Route::get('/sub-categories/{subCategory}/edit',[SubCategoryController::class,'edit'])->name('sub-categories.edit');
        Route::put('/sub-categories/{subCategory}',[SubCategoryController::class,'update'])->name('sub-categories.update');
        Route::delete('/sub-categories/{subCategory}',[SubCategoryController::class,'destroy'])->name('sub-categories.delete');

        // Brands routes
        Route::get('/brands',[BrandController::class,'index'])->name('brands.index');
        Route::get('/brands/create',[BrandController::class,'create'])->name('brands.create');
        Route::post('/brands/store',[BrandController::class,'store'])->name('brands.store');
        Route::get('/brands/{brand}/edit',[BrandController::class,'edit'])->name('brands.edit');
        Route::put('/brands/{brand}',[BrandController::class,'update'])->name('brands.update');
        Route::delete('/brands/{brand}',[BrandController::class,'destroy'])->name('brands.delete');

        // Product routes
        Route::get('/products',[ProductController::class,'index'])->name('products.index');
        Route::get('/products/create',[ProductController::class,'create'])->name('products.create');
        Route::post('/products/store',[ProductController::class,'store'])->name('products.store');
        Route::get('/products/{product}/edit',[ProductController::class,'edit'])->name('products.edit');
        Route::put('/products/{product}',[ProductController::class,'update'])->name('products.update');
        Route::get('/product-subcategories',[ProductSubCategoryController::class,'index'])->name('product-subcategories.index');
        Route::delete('/products/{product}',[ProductController::class,'destroy'])->name('products.delete');
        Route::get('/get-products',[ProductController::class,'getProducts'])->name('products.getProducts');

        // Image routes
        Route::post('/upload-temp-image',[TempImagesController::class,'create'])->name('temp-images.create');
        Route::post('/product-images/update',[ProductImageController::class,'update'])->name('product-images.update');
        Route::delete('/product-images/{image}',[ProductImageController::class,'destroy'])->name('product-images.delete');

        // Shipping routes
        Route::get('/shipping/create',[ShippingController::class,'create'])->name('shipping.create');
        Route::post('/shipping/store',[ShippingController::class,'store'])->name('shipping.store');
        Route::get('/shipping/{id}/edit',[ShippingController::class,'edit'])->name('shipping.edit');
        Route::put('/shipping/{id}',[ShippingController::class,'update'])->name('shipping.update');
        Route::delete('/shipping/{id}/delete',[ShippingController::class,'destroy'])->name('shipping.delete');


        // Coupon routes
        Route::get('/coupon',[DiscountCouponController::class,'index'])->name('coupons.index');
        Route::get('/coupon/create',[DiscountCouponController::class,'create'])->name('coupons.create');
        Route::post('/coupon/store',[DiscountCouponController::class,'store'])->name('coupons.store');
        Route::get('/coupon/{coupon}/edit',[DiscountCouponController::class,'edit'])->name('coupons.edit');
        Route::put('/coupon/{coupon}',[DiscountCouponController::class,'update'])->name('coupons.update');
        Route::delete('/coupon/{coupon}/delete',[DiscountCouponController::class,'destroy'])->name('coupons.delete');

        // Order routes
        Route::get('/orders',[OrderController::class,'index'])->name('orders.index');
        Route::get('/orders/detail/{id}',[OrderController::class,'detail'])->name('orders.detail');
        Route::post('/orders/change-status/{id}',[OrderController::class,'changeOrderStatus'])->name('orders.changeOrderStatus');
        Route::post('/orders/send-email/{id}',[OrderController::class,'sendInvoiceEmail'])->name('orders.sendInvoiceEmail');

        // User routes
        Route::get('/users',[UserController::class,'index'])->name('users.index');
        Route::get('/users/create',[UserController::class,'create'])->name('users.create');
        Route::post('/users/store',[UserController::class,'store'])->name('users.store');
        Route::get('/users/{userId}/edit',[UserController::class,'edit'])->name('users.edit');
        Route::put('/users/{userId}/update',[UserController::class,'update'])->name('users.update');
        Route::delete('/users/{userId}/delete',[UserController::class,'destroy'])->name('users.delete');

        // Pages routes
        Route::get('/pages',[PageController::class,'index'])->name('pages.index');
        Route::get('/pages/create',[PageController::class,'create'])->name('pages.create');
        Route::post('/pages/store',[PageController::class,'store'])->name('pages.store');
        Route::delete('/pages/{id}/delete',[PageController::class,'destroy'])->name('pages.delete');


        // Common routes
        Route::get('/getSlug',function(Request $request){
            $slug = '';
            if(!empty($request->title)) {
                $slug = Str::slug($request->title);
            }
            return response()->json([
                'status' => true,
                'slug' => $slug
            ]);
        })->name('getSlug');

    });
});