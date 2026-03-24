<?php

use App\Http\Controllers\CsvImportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CsvImportController::class, 'create'])->name('upload.create');
Route::post('/upload', [CsvImportController::class, 'store'])->name('upload.store');
Route::delete('/imports/{import}', [CsvImportController::class, 'destroy'])->name('imports.destroy');

Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
