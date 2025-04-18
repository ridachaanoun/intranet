<?php

use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\HallOfFameController;
use App\Http\Controllers\Loged_in_user;
use App\Http\Controllers\PointController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserInfoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserDetailsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PromotionController;

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
    Route::get('/classrooms/search', [ClassroomController::class, 'searchClassrooms']);
    Route::get('/users', [UserController::class, 'getAllUsers']);
    Route::get('/users/search', [UserController::class, 'searchUsers']);
    Route::get('/user/details', [Loged_in_user::class, 'getUserDetails']);
    Route::get('/user/classroom', [Loged_in_user::class, 'getUserClassroom']);
    Route::get('/user/cursus-history', [Loged_in_user::class, 'getUserCursusHistory']);
    Route::get('/user/cursus-history/{user}', [UserController::class, 'getUserCursusHistory']);
    Route::post('/teacher/assign-task', [TaskController::class, 'assignTask']);
    Route::get('/user-details/{user}', [UserController::class, 'getUserDetails']);
    Route::get('/user-classroom/{user}',[UserController::class,'getClassroomDetailsByUserId']);
    Route::post('/teacher/absences', [AbsenceController::class, 'addAbsence']);
    Route::get('/user/absences/{user}', [AbsenceController::class, 'getAbsenceDetailsByUserId']);
    Route::post('/teacher/assign-points', [PointController::class, 'assignPoints']);
    Route::post('/password/change', [AuthController::class, 'changePassword']);
    Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser']);
    Route::delete('/classrooms/{classroomId}/students/{studentId}', [ClassroomController::class, 'removeStudent']);
    Route::get('/classrooms/delegates', [ClassroomController::class, 'getClassroomDelegates']);
    Route::get('/hall-of-fame', [HallOfFameController::class, 'getTopStudents']);
    Route::get('/student/tasks/{student}', [TaskController::class, 'getTasksForStudent']);
    Route::get('/teacher/tasks/{teacher}', [TaskController::class, 'getTasksAssignedByTeacher']);
    Route::post('/admin/announcements', [AnnouncementController::class, 'store']);
    Route::get('/announcements', [AnnouncementController::class, 'index']);
    Route::get('/classroom/{id}', [ClassroomController::class, 'getClassroomById']);
    Route::get('/promotions', [PromotionController::class, 'getAllPromotions']);
});
