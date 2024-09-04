<?php

use App\Http\Controllers\SearchKeywordController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('search-keyword');
});
Route::get('/search-keyword', [SearchKeywordController::class, 'view'])->name('search-keyword');
Route::post('/search-keyword', [SearchKeywordController::class, 'search'])->name('search-keyword');
Route::get('/export', [SearchKeywordController::class, 'export'])->name('export');
