<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentGeneratorController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/documents/{document}/download', [DocumentGeneratorController::class, 'download'])->name('documents.download');
