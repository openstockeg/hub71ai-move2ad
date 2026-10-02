<?php

use App\Http\Controllers\BriefController;
use App\Http\Controllers\ScamCheckController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BriefController::class, 'create'])->name('home');
Route::post('brief', [BriefController::class, 'store'])->middleware('throttle:10,1')->name('briefs.store');
Route::get('brief/{brief}', [BriefController::class, 'show'])->name('briefs.show');
Route::post('brief/{brief}/generate', [BriefController::class, 'generate'])->middleware('throttle:10,1')->name('briefs.generate');

Route::get('check', [ScamCheckController::class, 'create'])->name('checks.create');
Route::post('check', [ScamCheckController::class, 'store'])->middleware('throttle:10,1')->name('checks.store');
Route::get('check/{check}', [ScamCheckController::class, 'show'])->name('checks.show');
Route::post('check/{check}/run', [ScamCheckController::class, 'run'])->middleware('throttle:10,1')->name('checks.run');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
