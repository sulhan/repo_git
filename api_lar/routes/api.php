<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

Route::post('/users/create',[UserController::class, 'create']);
Route::get('/users/search',[UserController::class, 'search']);