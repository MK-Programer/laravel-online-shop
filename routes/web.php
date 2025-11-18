<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SubCategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ProductController;

use App\Http\Controllers\Admin\CategorySubCategoryController;
use App\Http\Controllers\Admin\TempImagesController;
use App\Http\Controllers\Customer\HomeController as CustomerHomeController;
use App\Http\Controllers\Customer\ProductController as CustomerProductController;
use App\Http\Controllers\Customer\ShopController;
use App\Http\Controllers\Customer\CartController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [CustomerHomeController::class, 'index'])->name('customer.home');
Route::get('shop/{categorySlug?}/{subCategorySlug?}', [ShopController::class, 'index'])->name('customer.shop');
Route::get('product/{slug}', [CustomerProductController::class, 'index'])->name('customer.product');
Route::get('cart', [CartController::class, 'index'])->name('customer.cart');
Route::post('add-to-cart', [CartController::class, 'addToCart'])->name('customer.add-to-cart');

Route::prefix('admin')->group(function(){
    Route::middleware('admin.guest')->group(function(){
        Route::get('login', [AdminLoginController::class, 'index'])->name('admin.login');
        Route::post('authenticate', [AdminLoginController::class, 'authenticate'])->name('admin.authenticate');
    });

    Route::middleware('admin.auth')->group(function(){
        Route::get('dashboard', [HomeController::class, 'index'])->name('admin.dashboard');
        Route::get('logout', [HomeController::class, 'logout'])->name('admin.logout');
    
        //* Categories Routes
        Route::prefix('categories')->group(function(){
            Route::get('/', [CategoryController::class, 'index'])->name('admin.categories.index');
            Route::get('create', [CategoryController::class, 'create'])->name('admin.categories.create');
            Route::post('store', [CategoryController::class, 'store'])->name('admin.categories.store');
            Route::get('{record}/edit', [CategoryController::class, 'edit'])->name('admin.categories.edit');
            Route::put('{record}/update', [CategoryController::class, 'update'])->name('admin.categories.update');
            Route::delete('{record}/delete', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');
        });

        //* Sub Category Routes
        Route::prefix('sub-categories')->group(function(){
            Route::get('/', [SubCategoryController::class, 'index'])->name('admin.sub-categories.index');
            Route::get('create', [SubCategoryController::class, 'create'])->name('admin.sub-categories.create');
            Route::post('store', [SubCategoryController::class, 'store'])->name('admin.sub-categories.store');
            Route::get('{record}/edit', [SubCategoryController::class, 'edit'])->name('admin.sub-categories.edit');
            Route::put('{record}/update', [SubCategoryController::class, 'update'])->name('admin.sub-categories.update');
            Route::delete('{record}/delete', [SubCategoryController::class, 'destroy'])->name('admin.sub-categories.destroy');
        });

        //* Brand Routes
        Route::prefix('brands')->group(function(){
            Route::get('/', [BrandController::class, 'index'])->name('admin.brands.index');
            Route::get('create', [BrandController::class, 'create'])->name('admin.brands.create');
            Route::post('store', [BrandController::class, 'store'])->name('admin.brands.store');
            Route::get('{record}/edit', [BrandController::class, 'edit'])->name('admin.brands.edit');
            Route::put('{record}/update', [BrandController::class, 'update'])->name('admin.brands.update');
            Route::delete('{record}/delete', [BrandController::class, 'destroy'])->name('admin.brands.destroy');
        });

        //* Product Routes
        Route::prefix('products')->group(function(){
            Route::get('/', [ProductController::class, 'index'])->name('admin.products.index');
            Route::get('create', [ProductController::class, 'create'])->name('admin.products.create');
            Route::post('store', [ProductController::class, 'store'])->name('admin.products.store');
            Route::get('{record}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
            Route::put('{record}/update', [ProductController::class, 'update'])->name('admin.products.update');
            Route::delete('{record}/delete', [ProductController::class, 'destroy'])->name('admin.products.destroy');
            Route::get('search-products', [ProductController::class, 'searchProducts'])->name('admin.products.search-products');
        }); 

        //* Category Subcategory Routes
        Route::get('category-sub-categories', [CategorySubCategoryController::class, 'index'])->name('admin.category-sub-categories.index');

        //* Temp Image Upload Route
        Route::post('temp-image-upload', [TempImagesController::class, 'create'])->name('admin.temp-image-upload'); 
        Route::delete('temp-image-delete', [TempImagesController::class, 'delete'])->name('admin.temp-image-delete'); 
    });
});


