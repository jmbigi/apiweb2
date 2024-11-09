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

Route::get('/lang/{lang}', [HomeController::class, 'index']);

Route::get('/{lang}', [HomeController::class, 'index'])->where('lang', '[a-z]{2}');

Route::get('/{lang}/score/{name}', [MusicScoreController::class, 'showByLandAndName'])
    ->name('la-score-viewbyname')->where('lang', '[a-z]{2}');;

Route::get('/{lang}/styles/{stylename}/{scorename}', [MusicScoreController::class, 'showByLandAndStyleAndScoreName'])
    ->name('la-score-viewbystyleandscorename')->where('lang', '[a-z]{2}');;

Route::get('/{lang}/instruments/{instrumentname}/{scorename}', [MusicScoreController::class, 'showByLandAndInstrumentAndScoreName'])
    ->name('la-score-viewbyinstrumentandscorename')->where('lang', '[a-z]{2}');;
