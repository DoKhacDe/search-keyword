<?php

use App\Http\Controllers\SearchKeywordController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('search-keyword');
});

Route::get('/', [SearchKeywordController::class, 'view'])->name('search-keyword');
