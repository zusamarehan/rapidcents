<?php

use App\Http\Controllers\CreateTransactionController;
use App\Http\Controllers\RetrieveTransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/transactions/{id}', RetrieveTransactionController::class);
Route::post('/transactions', CreateTransactionController::class);
