<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileUploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', [FileUploadController::class, 'showUploadForm'])->name('upload-form');
Route::post('/upload', [FileUploadController::class, 'uploadCsv'])->name('upload-csv');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/logs/{upload}', [DashboardController::class, 'logs'])->name('dashboard.logs');
