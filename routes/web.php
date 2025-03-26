<?php

use App\Http\Controllers\authController;
use Illuminate\Support\Facades\Route;

Route::get("/verif/email/{token}", [authController::class, 'verifEmail']);
