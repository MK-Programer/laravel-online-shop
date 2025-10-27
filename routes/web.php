<?php

use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\TempImagesController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

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
            // Route::delete('/{id}/delete', [CategoriesController::class, 'delete'])->name('admin.categories.delete');
        });

        //* Temp Image Upload Route
        Route::post('temp-image-upload', [TempImagesController::class, 'create'])->name('admin.temp-image-upload'); 
        Route::delete('temp-image-delete', [TempImagesController::class, 'delete'])->name('admin.temp-image-delete'); 
    });
});


