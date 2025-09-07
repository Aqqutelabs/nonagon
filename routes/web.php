<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

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

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
