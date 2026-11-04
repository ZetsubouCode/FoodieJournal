<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlaceController;
use App\Http\Controllers\Admin\SuggestionController;
use App\Http\Controllers\Admin\TaxonomyController;
use App\Http\Controllers\Admin\VisitController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/explore', [PublicController::class, 'explore'])->name('explore.index');
Route::get('/explore/count', [PublicController::class, 'count'])->middleware('throttle:90,1,explore-count:');
Route::get('/visit/{slug}', [PublicController::class, 'visit'])->name('visit.show');
Route::get('/place/{slug}', [PublicController::class, 'place'])->name('place.show');
Route::get('/sitemap.xml', [PublicController::class, 'sitemap']);
Route::get('/robots.txt', [PublicController::class, 'robots']);

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthController::class, 'show'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'login'])->middleware('throttle:10,1,admin-login:');
});
Route::get('/media/{uuid}/{variant}', [MediaController::class, 'show'])->whereUuid('uuid')->middleware('throttle:240,1,media:');
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('/', DashboardController::class);
    Route::resource('place', PlaceController::class)->except('show');
    Route::resource('visit', VisitController::class)->except('show');
    Route::get('visit/{visit}/preview', [VisitController::class, 'preview']);
    Route::get('taxonomy', [TaxonomyController::class, 'index']);
    Route::get('taxonomy/{kind}', [TaxonomyController::class, 'index']);
    Route::post('taxonomy/{kind}', [TaxonomyController::class, 'store']);
    Route::put('taxonomy/{kind}/{id}', [TaxonomyController::class, 'update']);
    Route::delete('taxonomy/{kind}/{id}', [TaxonomyController::class, 'destroy']);
    Route::get('suggestions/{kind}', SuggestionController::class);
    Route::post('visit/{visit}/media', [MediaController::class, 'store']);
    Route::post('media/{media}/retry', [MediaController::class, 'retry']);
    Route::put('media/{media}', [MediaController::class, 'update']);
    Route::delete('media/{media}', [MediaController::class, 'destroy']);
});
