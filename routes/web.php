<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\BriefController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\ScamCheckController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BriefController::class, 'create'])->name('home');
Route::post('brief', [BriefController::class, 'store'])->middleware('throttle:ai')->name('briefs.store');
Route::get('brief/{brief}', [BriefController::class, 'show'])->name('briefs.show');
Route::post('brief/{brief}/generate', [BriefController::class, 'generate'])->middleware('throttle:ai')->name('briefs.generate');

Route::get('check', [ScamCheckController::class, 'create'])->name('checks.create');
Route::post('check', [ScamCheckController::class, 'store'])->middleware('throttle:ai')->name('checks.store');
Route::get('check/{check}', [ScamCheckController::class, 'show'])->name('checks.show');
Route::post('check/{check}/run', [ScamCheckController::class, 'run'])->middleware('throttle:ai')->name('checks.run');

Route::post('q', [AnswerController::class, 'store'])->middleware('throttle:ai')->name('answers.store');
Route::get('q/{answer}', [AnswerController::class, 'show'])->name('answers.show');
Route::post('q/{answer}/generate', [AnswerController::class, 'generate'])->middleware('throttle:ai')->name('answers.generate');

Route::get('data', [DataController::class, 'index'])->name('data');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
