<?php

use App\Http\Controllers\MusicScoreController;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/score/{name}', [MusicScoreController::class, 'showByName'])
    ->name('score-viewbyname');

Route::get('/styles/{stylename}/{scorename}', [MusicScoreController::class, 'showByStyleAndScoreName'])
    ->name('score-viewbystyleandscorename');

Route::get('/instruments/{instrumentname}/{scorename}', [MusicScoreController::class, 'showByInstrumentAndScoreName'])
    ->name('score-viewbyinstrumentandscorename');
