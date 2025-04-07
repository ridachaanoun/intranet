<?php

use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\Loged_in_user;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
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
    Route::post('admin/classes', [ClassroomController::class, 'createClassroom']);
    Route::post('admin/classrooms/{classroom}/students', [ClassroomController::class, 'addStudents']);
    Route::put('admin/classrooms/{classroom}', [ClassroomController::class, 'updateClassroom']);
    Route::delete('admin/classrooms/{classroom}', [ClassroomController::class, 'deleteClassroom']);
    Route::get('/classrooms', [ClassroomController::class, 'index']);
    Route::get('/users', [UserController::class, 'getAllUsers']);
    Route::get('/user/details', [Loged_in_user::class, 'getUserDetails']);
    Route::get('/user/classroom', [Loged_in_user::class, 'getUserClassroom']);
    Route::get('/user/cursus-history', [Loged_in_user::class, 'getUserCursusHistory']);
    Route::post('/teacher/assign-task', [TaskController::class, 'assignTask']);
});