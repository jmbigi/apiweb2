<?php

use App\Http\Controllers\MusicScoreController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SitemapController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/score/{name}', [MusicScoreController::class, 'showByName'])
    ->name('score-viewbyname');

Route::get('/styles/{stylename}/{scorename}', [MusicScoreController::class, 'showByStyleAndScoreName'])
    ->name('score-viewbystyleandscorename');

Route::get('/instruments/{instrumentname}/{scorename}', [MusicScoreController::class, 'showByInstrumentAndScoreName'])
    ->name('score-viewbyinstrumentandscorename');

Route::get('/lang/{lang}', [HomeController::class, 'index']);

Route::get('/{lang}', [HomeController::class, 'index'])->where('lang', '[a-z]{2}');

Route::get('/{lang}/score/{name}', [MusicScoreController::class, 'showByLangAndName'])
    ->name('la-score-viewbyname')->where('lang', '[a-z]{2}');;

Route::get('/{lang}/styles/{stylename}/{scorename}', [MusicScoreController::class, 'showByLangAndStyleAndScoreName'])
    ->name('la-score-viewbystyleandscorename')->where('lang', '[a-z]{2}');;

Route::get('/{lang}/instruments/{instrumentname}/{scorename}', [MusicScoreController::class, 'showByLangAndInstrumentAndScoreName'])
    ->name('la-score-viewbyinstrumentandscorename')->where('lang', '[a-z]{2}');;


Route::get('/sitemap', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/sitemap/{lang}', [SitemapController::class, 'index'])->where('lang', '[a-z]{2}');
