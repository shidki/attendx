<?php

use App\Http\Controllers\authController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [authController::class , 'login']);
Route::post('/register', [authController::class , 'register']);
