<?php

use App\Http\Controllers\SearchKeywordController;
use Illuminate\Support\Facades\Route;


Route::get('/search', [SearchKeywordController::class, 'view'])->name('form-search-keyword');
Route::post('/search-keyword', [SearchKeywordController::class, 'search'])->name('post-search-keyword');
Route::post('/save-data', [SearchKeywordController::class, 'saveData'])->name('save-data');
Route::get('/export', [SearchKeywordController::class, 'export'])->name('export');
