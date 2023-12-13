<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\BookController;
use \App\Http\Controllers\Api\AuthController;
use \App\Http\Controllers\Api\UserController;
use \App\Http\Controllers\Api\TakingController;
use \App\Http\Controllers\Api\AdminController;
use \App\Http\Middleware\TokenIsValid;


Route::apiResources([
    "books" => BookController::class,
    "taking" => TakingController::class
]);

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

//Auth User Rotes
Route::get('user/book', [UserController::class, 'getBooksUser']);
Route::delete('user/book/{id}', [UserController::class, 'destroy']);


// Admin
Route::get('admin', [AdminController::class, 'getAdmin'])->middleware(TokenIsValid::class);
Route::get('admin/block/users', [AdminController::class, 'getUsers']); // http://127.0.0.1:8000/api/admin/block/users
Route::post('admin/block', [AdminController::class, 'blockUser']); // http://127.0.0.1:8000/api/admin/block/id
Route::post('admin/unlock', [AdminController::class, 'unlockUser']); // http://127.0.0.1:8000/api/admin/unlock/id
