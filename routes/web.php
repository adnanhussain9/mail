<?php

use App\Http\Controllers\MailLogController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', [MailLogController::class, 'index'])->name('dashboard');
    Route::post('/settings', [MailLogController::class, 'updateSettings'])->name('settings.update');
    Route::post('/process-sheet', [MailLogController::class, 'processSheet'])->name('process.sheet');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
