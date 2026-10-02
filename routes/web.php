<?php

use App\Http\Controllers\BriefController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BriefController::class, 'create'])->name('home');
Route::post('brief', [BriefController::class, 'store'])->middleware('throttle:10,1')->name('briefs.store');
Route::get('brief/{brief}', [BriefController::class, 'show'])->name('briefs.show');
Route::post('brief/{brief}/generate', [BriefController::class, 'generate'])->middleware('throttle:10,1')->name('briefs.generate');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
