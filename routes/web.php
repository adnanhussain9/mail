<?php

use App\Http\Controllers\MailLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OnboardingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', [MailLogController::class, 'index'])->name('dashboard');
    
    // Onboarding routes
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');
    Route::post('/onboarding', [OnboardingController::class, 'complete'])->name('onboarding.complete');

    // Configuration routes
    Route::get('/configuration', [MailLogController::class, 'settings'])->name('configuration');
    Route::get('/email-content', [MailLogController::class, 'emailContent'])->name('email.content');
    Route::post('/settings', [MailLogController::class, 'updateSettings'])->name('settings.update');
    
    // Sheet processing
    Route::post('/process-sheet', [MailLogController::class, 'processSheet'])->name('process.sheet');
    Route::post('/add-to-sheet', [MailLogController::class, 'addToSheet'])->name('sheet.add');
    Route::get('/view-sheet', [MailLogController::class, 'viewSheet'])->name('sheet.view');
    
    // AI Email Generation
    Route::post('/generate-email', [MailLogController::class, 'generateEmailBody'])->name('email.generate');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Testing endpoints
    Route::post('/test-connection', [\App\Http\Controllers\ConnectionTestController::class, 'testSmtp'])->name('test.connection');
    Route::post('/test-sheet', [\App\Http\Controllers\ConnectionTestController::class, 'testSheet'])->name('test.sheet');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [\App\Http\Controllers\AdminController::class, 'index'])->name('users.index');
    Route::post('/users', [\App\Http\Controllers\AdminController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [\App\Http\Controllers\AdminController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [\App\Http\Controllers\AdminController::class, 'destroy'])->name('users.destroy');
});

require __DIR__ . '/auth.php';
