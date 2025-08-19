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

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
