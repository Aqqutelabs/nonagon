<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Equipment\EquipmentController;
use App\Models\EquipmentCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/second', function () {
    return view('welcome');
});

Route::get('/', function () {
    return view('second');
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

// Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
Route::post('/email/resend', [VerificationController::class, 'resend'])->name('verification.resend');

Route::post('/send/invite', [AuthController::class, 'sendInvite'])->name('send.invite');
Route::get('/accept/invite', [AuthController::class, 'acceptInvite'])->name('accept.invite');
Route::post('/complete/invite', [AuthController::class, 'completeInvite'])->name('complete.invite');

Route::get('/equipment/category', [EquipmentCategory::class, 'index'])->name('equipmentcategory.index');
Route::post('/equipment/category', [EquipmentCategory::class, 'store'])->name('equipmentcategory.store');
Route::resource('equipments', EquipmentController::class);
