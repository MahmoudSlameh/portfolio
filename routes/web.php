<?php

use App\Http\Controllers\Site\ArticleController;
use App\Http\Controllers\Site\ContactMessageController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\ProjectController;
use App\Http\Controllers\Site\SeoController;
use App\Http\Middleware\EnsurePageEnabled;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{slug}', [ProjectController::class, 'show'])->name('projects.show');

Route::middleware(EnsurePageEnabled::class.':writing')->group(function (): void {
    Route::get('/writing', [ArticleController::class, 'index'])->name('writing.index');
    Route::get('/writing/{slug}', [ArticleController::class, 'show'])->name('writing.show');
});

Route::get('/books', [PageController::class, 'books'])->middleware(EnsurePageEnabled::class.':books')->name('books');
Route::get('/uses', [PageController::class, 'uses'])->middleware(EnsurePageEnabled::class.':uses')->name('uses');
Route::get('/now', [PageController::class, 'now'])->middleware(EnsurePageEnabled::class.':now')->name('now');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/rss.xml', [SeoController::class, 'rss'])->name('rss');

Route::post('/contact', [ContactMessageController::class, 'store'])->middleware('throttle:contact')->name('contact.store');

Route::fallback(fn () => abort(404));
