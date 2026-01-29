<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentGeneratorController;

Route::get('/', function () {
    return to_route('documents.index');
});

Route::prefix('documents')->controller(DocumentGeneratorController::class)->group(function () {
    Route::get('/', 'index')->name('documents.index');
    Route::get('/create', 'create')->name('documents.create');
    Route::post('/', 'store')->name('documents.store');

    Route::get('/{document}/download', 'download')->name('documents.download');
    Route::get('/{document}/edit', 'edit')->name('documents.edit');
    Route::get('/{document}/show', 'show')->name('documents.show');
    Route::delete('/{document}', 'destroy')->name('documents.destroy');
    Route::get('/{document}/edit-simple', 'editSimple')->name('documents.edit-simple');
    Route::patch('/{document}/config', 'saveConfig');
});
