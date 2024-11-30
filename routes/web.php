<?php

use App\Http\Controllers\MusicScoreController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\MockUpController;
use App\Http\Controllers\SiteStatisticsController;

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

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

Route::get('/sitemap/{lang}', [SitemapController::class, 'index'])->where('lang', '[a-z]{2}')->name('sitemapLang');;


Route::get('/pdf/{locale}/{name}', [MockUpController::class, 'generatePdf'])->name('getPdfByLangAndName');
Route::get('/image/{locale}/{name}', [MockUpController::class, 'generateImage'])->name('showImageByLangAndName');
Route::get('/page/{locale}/{name}', [MockUpController::class, 'showPage'])->name('showPageByLangAndName');

Route::get('/stats', [SiteStatisticsController::class, 'index'])->name('stats')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
