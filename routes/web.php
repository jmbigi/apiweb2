<?php

use mikehaertl\pdftk\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\admin\ProfileController;
use App\Http\Controllers\admin\ComposerController;
use App\Http\Controllers\admin\MusicScoreController;
use App\Http\Controllers\admin\FamilyInstrumentController;
use App\Http\Controllers\admin\InstrumentController;
use App\Http\Controllers\admin\StyleMusicController;
use App\Http\Controllers\admin\SubscriptionController;
use App\Http\Controllers\admin\SubscribedUserController;
use App\Http\Controllers\admin\ComposerRequestController;
use App\Http\Controllers\SupportController;

use App\Exports\UsersSuscritosExport;
use App\Exports\UsoExport;
use App\Exports\PersonalLogsExport;
use Maatwebsite\Excel\Facades\Excel;

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

Route::get('/', function () {
  return view('home');
})->name('home');


Route::group(['middleware' => ['auth', 'verified', 'check_token']], function () {
  Route::get('/dashboard', [PagesController::class, 'index'])->name('dashboard');
});
// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified','check_token'])->name('dashboard');

Route::get('/datatables', [PagesController::class, 'datatables']);
Route::get('/ktdatatables', [PagesController::class, 'ktDatatables']);
Route::get('/select2', [PagesController::class, 'select2']);
Route::get('/jquerymask', [PagesController::class, 'jQueryMask']);
Route::get('/icons/custom-icons', [PagesController::class, 'customIcons']);
Route::get('/icons/flaticon', [PagesController::class, 'flaticon']);
Route::get('/icons/fontawesome', [PagesController::class, 'fontawesome']);
Route::get('/icons/lineawesome', [PagesController::class, 'lineawesome']);
Route::get('/icons/socicons', [PagesController::class, 'socicons']);
Route::get('/icons/svg', [PagesController::class, 'svg']);

// Quick search dummy route to display html elements in search dropdown (header search)
Route::get('/quick-search', [PagesController::class, 'quickSearch'])->name('quick-search');


Route::get('/support/en', [SupportController::class, 'index'])->name('support-en');
Route::get('/sendemail-support/en', [SupportController::class, 'sendEmail'])->name('send-email-support-en');


Route::middleware(['auth', 'role:superadmin', 'check_token'])->group(function () {
  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit')->middleware('permission:update-profile');
  Route::get('/profile/change_password', [ProfileController::class, 'change_password'])->name('profile.change_password')->middleware('permission:update-profile');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

  Route::get('/users', [UserController::class, 'index'])->name('user.index');
  Route::post('/get_users', [UserController::class, 'index'])->name('get_users');
  Route::get('user/create', [UserController::class, 'create'])->name('user.create');
  Route::post('user/store', [UserController::class, 'store'])->name('user.store');
  Route::get('user/edit/{id}', [UserController::class, 'edit'])->name('user.edit');
  Route::patch('user/update/{id}', [UserController::class, 'update'])->name('user.update');
  Route::post('/change_user_status', [UserController::class, 'change_user_status'])->name('change_user_status');
  Route::delete('/delete_user/{id}', [UserController::class, 'destroy']);
  Route::get('/admin/users/{id}/send-user-registered-email', [UserController::class, 'sendUserRegisteredEmail'])->name('user.sendUserRegisteredEmail');
  Route::get('/admin/users/{id}/send-plan-offer-email', [UserController::class, 'sendPlanOfferEmail'])->name('user.sendPlanOfferEmail');

  Route::get('/composers', [ComposerController::class, 'index'])->name('composer.index');
  Route::post('/get_composers', [ComposerController::class, 'index'])->name('get_composers');
  Route::get('composer/show/{id}', [ComposerController::class, 'show'])->name('composer.show');
  Route::post('/update_composer_status/{id}', [ComposerController::class, 'update_composer_status'])->name('update_composer_status');

  Route::get('/composer_request', [ComposerRequestController::class, 'index'])->name('composer_request.index');
  Route::post('/get_composers_request', [ComposerRequestController::class, 'index'])->name('get_composers_request');
  Route::get('composer_request/edit/{id}', [ComposerRequestController::class, 'edit'])->name('composer_request.edit');
  Route::patch('composer_request/update/{id}', [ComposerRequestController::class, 'update'])->name('composer_request.update');
  Route::post('/change_composer_status', [ComposerRequestController::class, 'change_composer_status']);
  Route::delete('/delete-composer-request/{id}', [ComposerRequestController::class, 'destroy']);

  // Route::post('/store_denied_reason', [ComposerRequestController::class, 'store_denied_reason'])->name('store_denied_reason');
  Route::get('/subscription-plan', [SubscriptionController::class, 'index'])->name('subscription.index');
  // Route::post('/get_composers_request', [ComposerRequestController::class, 'index'])->name('get_composers_request');
  Route::get('subscription-plan/create', [SubscriptionController::class, 'create'])->name('subscription.create');
  Route::post('subscription-plan/store', [SubscriptionController::class, 'store'])->name('subscription.store');
  Route::get('subscription-plan/edit/{id}', [SubscriptionController::class, 'edit'])->name('subscription.edit');
  Route::post('subscription-plan/update/{id}', [SubscriptionController::class, 'update'])->name('subscription.update');
  Route::delete('/delete-subscription-plan/{id}', [SubscriptionController::class, 'destroy']);
  Route::post('/change_subscription_status', [SubscriptionController::class, 'change_subscription_status'])->name('change_subscription_status');


  Route::get('/subscribed-user', [SubscribedUserController::class, 'index'])->name('subscribed_user.index');
  Route::post('/get-subscribed-user', [SubscribedUserController::class, 'index'])->name('get_subscribed_user');


  Route::get('/music-score', [MusicScoreController::class, 'index'])->name('music_score.index');
  Route::post('/get-music-score', [MusicScoreController::class, 'index'])->name('get_music_score');
  Route::get('music-score/show/{id}', [MusicScoreController::class, 'show'])->name('music_score.show');
  Route::post('/change-music-score-status', [MusicScoreController::class, 'change_music_score_status'])->name('change_music_score_status');
  Route::post('/music-score-status-update/{id}', [MusicScoreController::class, 'music_score_status_update'])->name('music_score_status_update');
  Route::delete('/delete-music-score/{id}', [MusicScoreController::class, 'destroy']);

  Route::get('/instruments', [InstrumentController::class, 'index'])->name('instrument.index');
  Route::post('/get-instruments', [InstrumentController::class, 'index'])->name('get_instruments');
  Route::get('instrument/create', [InstrumentController::class, 'create'])->name('instrument.create');
  Route::post('instrument/store', [InstrumentController::class, 'store'])->name('instrument.store');
  Route::get('instrument/edit/{id}', [InstrumentController::class, 'edit'])->name('instrument.edit');
  Route::patch('instrument/update/{id}', [InstrumentController::class, 'update'])->name('instrument.update');
  Route::delete('/delete-instrument/{id}', [InstrumentController::class, 'destroy']);
  Route::post('/change-instrument-status', [InstrumentController::class, 'change_instrument_status'])->name('change_instrument_status');


  Route::get('/style-music', [StyleMusicController::class, 'index'])->name('style_music.index');
  Route::post('/get-style-music', [StyleMusicController::class, 'index'])->name('get_style_music');
  Route::get('style-music/create', [StyleMusicController::class, 'create'])->name('style_music.create');
  Route::post('style-music/store', [StyleMusicController::class, 'store'])->name('style_music.store');
  Route::get('style-music/edit/{id}', [StyleMusicController::class, 'edit'])->name('style_music.edit');
  Route::patch('style-music/update/{id}', [StyleMusicController::class, 'update'])->name('style_music.update');
  Route::delete('/delete-style-music/{id}', [StyleMusicController::class, 'destroy']);
  Route::post('/change-style-status', [StyleMusicController::class, 'change_style_music_status'])->name('change_style_music_status');

  Route::get('/family-instruments', [FAmilyInstrumentController::class, 'index'])->name('family_instruments.index');
  Route::post('/get-family-instruments', [FAmilyInstrumentController::class, 'index'])->name('get_family_instruments');
  Route::get('family-instrument/create', [FAmilyInstrumentController::class, 'create'])->name('family_instrument.create');
  Route::post('family-instrument/store', [FAmilyInstrumentController::class, 'store'])->name('family_instrument.store');
  Route::get('family-instrument/edit/{id}', [FAmilyInstrumentController::class, 'edit'])->name('family_instrument.edit');
  Route::patch('family-instrument/update/{id}', [FAmilyInstrumentController::class, 'update'])->name('family_instrument.update');
  Route::delete('/delete-family-instrument/{id}', [FAmilyInstrumentController::class, 'destroy']);
  Route::post('/change-faimily-instrument-status', [FAmilyInstrumentController::class, 'change_family_instrument_status'])->name('change_family_instrument_status');

  // Exportar logs personales a Excel - CORREGIDO
  Route::get('/exportar-uso-offline', function () {
    return Excel::download(new PersonalLogsExport, 'uso_offline.xlsx');
  })->name('exportar_uso_offline');

  Route::get('/exportar-suscritos', function () {
    return Excel::download(new UsersSuscritosExport, 'usuarios_suscritos.xlsx');
  })->name('exportar_usuarios_suscritos');

  Route::get('/exportar-uso', function () {
    return Excel::download(new UsoExport, 'uso_usuarios.xlsx');
  })->name('exportar_uso');
});

Route::get('/subscription-payment/{id}', [SubscriptionController::class, 'subscription_payment'])->name('subscription.payment');
Route::post('subscription-order', [SubscriptionController::class, 'subscription_order'])->name('subscription.order');
Route::get('subscription-status', [SubscriptionController::class, 'subscription_status'])->name('subscription.status');
// Route::middleware(['auth','check_token' ])->group(function () {    

// });
Route::post('paypal-webhook', [SubscriptionController::class, 'paypal_webhook'])->name('paypalWebhook');
Route::get('plan-renew-email', [SubscriptionController::class, 'paypalRenewEmail'])->name('paypalRenewEmail');



require __DIR__ . '/auth.php';
