<?php

use App\Http\Controllers\ClassroomController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserDetailsController;
use App\Http\Controllers\AdminController;



Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::get("/user",[AuthController::class,"user"]);
    Route::put('/user/{user}/personal-info', [UserDetailsController::class, 'updatePersonalInfo']);
    Route::put('/user/{user}/account-info', [UserDetailsController::class, 'updateAccountInfo']);
    Route::put('/user/{user}/profile', [UserDetailsController::class, 'updateProfile']);
    Route::put('/admin/user/{user}/role', [AdminController::class, 'updateUserRole']);
    Route::post('/admin/user/{user}/upload-image', [AdminController::class, 'uploadImage']);
    Route::post('/classes', [ClassroomController::class, 'createClassroom']);
    
});