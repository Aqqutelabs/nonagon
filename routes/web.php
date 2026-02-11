<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/second', function () {
    return view('welcome');
});

Route::get('/', function () {
    return view('second');
});


// Admin routes - require authentication
Route::middleware(['auth'])->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    
    Route::get('/equipment', function () {
        return view('admin.equipment');
    });

    Route::get('/maintenance', function () {
        return view('admin.maintenance');
    });

    Route::get('/users',function () {
        return view('admin.users');
    });

    Route::get('/reports',function () {
        return view('admin.reports');
    });

    Route::get('/settings',function () {
        return view('admin.settings');
    });

    Route::get('/profile',function () {
        return view('admin.profile');
    });

    Route::get('/account',function () {
        return view('admin.account');
    });
});


Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
