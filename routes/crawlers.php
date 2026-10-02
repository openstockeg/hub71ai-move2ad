<?php

use App\Http\Controllers\DataController;
use Illuminate\Support\Facades\Route;

/*
 * Plain-text and XML files for crawlers and AI assistants. Loaded without the
 * web middleware group: no session row or cookies for every bot request.
 */
Route::get('sitemap.xml', [DataController::class, 'sitemap'])->name('sitemap');
Route::get('llms.txt', [DataController::class, 'llms'])->name('llms');
Route::get('robots.txt', [DataController::class, 'robots'])->name('robots');
