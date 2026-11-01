<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\PublicQuoteController;
use Illuminate\Support\Facades\Route;

Route::pattern('id', '[0-9]+');
Route::view('/', 'landing')->name('home');
Route::view('/beta', 'beta');
Route::view('/marca', 'brand')->name('brand');
Route::get('/feedback', [FeedbackController::class, 'show']);
Route::post('/feedback', [FeedbackController::class, 'store'])->middleware('throttle:5,60,feedback:');
Route::post('/pilot', [AuthController::class, 'pilot'])->middleware('throttle:3,60,pilot:');
Route::middleware('guest')->group(function () {
    Route::view('/register', 'auth.register')->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,60,register:');
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1,login:');
    Route::post('/demo', [AuthController::class, 'demo'])->middleware('throttle:5,60,demo:');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::get('/p/{token}', [PublicQuoteController::class, 'show'])->middleware('throttle:60,1,public-view:');
Route::post('/p/{token}', [PublicQuoteController::class, 'decide'])->middleware('throttle:8,1,public-decision:');
Route::prefix('app')->middleware(['auth', 'business'])->group(function () {
    Route::get('/', [AppController::class, 'dashboard']);
    Route::get('/customers', [AppController::class, 'customerIndex']);
    Route::get('/customers/new', [AppController::class, 'customerForm']);
    Route::post('/customers', [AppController::class, 'customerSave']);
    Route::get('/customers/{id}', [AppController::class, 'customerForm'])->whereNumber('id');
    Route::post('/customers/{id}', [AppController::class, 'customerSave'])->whereNumber('id');
    Route::post('/customers/{id}/archive', [AppController::class, 'customerArchive']);
    Route::get('/quotes', [AppController::class, 'quoteIndex']);
    Route::get('/quotes/new', [AppController::class, 'quoteForm']);
    Route::post('/quotes', [AppController::class, 'quoteSave']);
    Route::get('/quotes/{id}', [AppController::class, 'quoteShow'])->whereNumber('id');
    Route::get('/quotes/{id}/edit', [AppController::class, 'quoteForm']);
    Route::post('/quotes/{id}', [AppController::class, 'quoteSave'])->whereNumber('id');
    Route::post('/quotes/{id}/publish', [AppController::class, 'publish']);
    Route::post('/quotes/{id}/duplicate', [AppController::class, 'duplicate']);
    Route::post('/quotes/{id}/convert', [AppController::class, 'convert']);
    Route::post('/versions/{id}/revoke', [AppController::class, 'revoke']);
    Route::get('/versions/{id}/print', [AppController::class, 'versionPrint']);
    Route::get('/jobs', [AppController::class, 'jobIndex']);
    Route::get('/jobs/new', [AppController::class, 'jobForm']);
    Route::post('/jobs', [AppController::class, 'jobSave']);
    Route::get('/jobs/{id}', [AppController::class, 'jobShow'])->whereNumber('id');
    Route::get('/jobs/{id}/edit', [AppController::class, 'jobForm']);
    Route::post('/jobs/{id}', [AppController::class, 'jobSave'])->whereNumber('id');
    Route::post('/jobs/{id}/payments', [AppController::class, 'pay']);
    Route::post('/payments/{id}/void', [AppController::class, 'void']);
    Route::get('/settings', [AppController::class, 'settings']);
    Route::post('/settings', [AppController::class, 'settingsSave']);
    Route::get('/export/{type}', ExportController::class)->middleware('throttle:20,1,export:');
});
