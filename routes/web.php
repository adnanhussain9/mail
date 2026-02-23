<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\MailLogController::class, 'index'])->name('dashboard');
Route::post('/settings', [\App\Http\Controllers\MailLogController::class, 'updateSettings'])->name('settings.update');
