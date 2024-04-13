<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\UserBookController;
use App\Http\Middleware\TokenIsValid;
use Illuminate\Support\Facades\Route;


Route::get('book', [BookController::class, 'getAll']);


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);


//Auth User Rotes
Route::post('user/book', [UserBookController::class, 'create'])->middleware(TokenIsValid::class);
Route::get('user/book', [UserBookController::class, 'get'])->middleware(TokenIsValid::class);
Route::delete('user/book/{id}', [UserBookController::class, 'delete'])->middleware(TokenIsValid::class);


// Admin
Route::get('admin', [AdminController::class, 'getAdmin'])->middleware(['token', 'isAdmin']);
Route::get('admin/block/users', [AdminController::class, 'getUsers'])->middleware(['token', 'isAdmin']); // http://127.0.0.1:8000/api/admin/block/users
Route::post('admin/block', [AdminController::class, 'blockUser'])->middleware(['token', 'isAdmin']); // http://127.0.0.1:8000/api/admin/block/id
Route::post('admin/unlock', [AdminController::class, 'unlockUser'])->middleware(['token', 'isAdmin']); // http://127.0.0.1:8000/api/admin/unlock/id
Route::delete('admin/book/{id}', [BookController::class, 'destroy'])->middleware(['token', 'isAdmin']); // http://127.0.0.1:8000/api/admin/unlock/id
Route::post('admin/book', [BookController::class, 'create'])->middleware(['token', 'isAdmin']); // http://127.0.0.1:8000/api/admin/unlock/id
Route::patch('admin/book/{id}', [BookController::class, 'update'])->middleware(['token', 'isAdmin']); // http://127.0.0.1:8000/api/admin/unlock/id
