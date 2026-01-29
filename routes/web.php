<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentGeneratorController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/documents/{document}/download', [DocumentGeneratorController::class, 'download'])->name('documents.download');
Route::get('/documents/{document}/edit', [DocumentGeneratorController::class, 'edit'])->name('documents.edit');
Route::get('/documents/{document}/edit-simple', [DocumentGeneratorController::class, 'editSimple'])->name('documents.edit-simple');
Route::patch('/documents/{document}/config', [DocumentGeneratorController::class, 'saveConfig']);
