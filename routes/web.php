<?php

use App\Http\Controllers\MusicScoreController;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/score/{name}', [MusicScoreController::class, 'showByName'])
    ->name('score-viewbyname');
