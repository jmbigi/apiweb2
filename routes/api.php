<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\ComposerController;
use App\Http\Controllers\EnsembleController;
use App\Http\Controllers\FamilyInstrumentController;
use App\Http\Controllers\InstrumentController;
use App\Http\Controllers\MusicScoreController;
use App\Http\Controllers\StyleMusicController;
use App\Http\Controllers\ComposerRequestController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\InAppSubscriptionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public section
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'loginUser']);
    Route::prefix('user')->group(function () {
        Route::post('/signup', [AuthController::class, 'createUser']);
        Route::get('/verify/resend', [AuthController::class, 'resendEmailVerify']);
        Route::get('/verify/activate', [AuthController::class, 'activateUser'])->name('verification.verify');
    });
});

// Auth section
Route::prefix('auth')->group(function () {
    Route::prefix('user')->group(function () {
        Route::post('/request-otp', [AuthController::class, 'requestOtp']);
        Route::post('/resend-request-otp', [AuthController::class, 'requestOtp']);
        Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('/change-password', [AuthController::class, 'setNewPasword']);
        Route::get('/', [AuthController::class, 'getUser']);
    });
});

// Anonymous Routes for App
// Composer section
Route::prefix('composer')->group(function () {
    Route::get('/list', [ComposerController::class, 'getAll']);
    Route::get('/{composer}', [ComposerController::class, 'show']);
});

// Instruments section
Route::prefix('instruments')->group(function () {
    Route::get('/list', [InstrumentController::class, 'getAll']);
    Route::get('/{instrument}', [InstrumentController::class, 'show']);
    Route::prefix('family')->group(function () {
        Route::get('/list', [FamilyInstrumentController::class, 'getAll']); //TODO: falta crear el permiso
        Route::get('/{family}', [FamilyInstrumentController::class, 'show']); //TODO: falta crear el permiso
    });
});

// Style-music section
Route::prefix('style-music')->group(function () {
    Route::get('/list', [StyleMusicController::class, 'getAll']);
    Route::get('/{style}', [StyleMusicController::class, 'show']);
});

// Music-store section
Route::prefix('music-score')->group(function () {
    Route::get('/list', [MusicScoreController::class, 'getList']);
    Route::get('/get/{get}', [MusicScoreController::class, 'get']);
    Route::get('/list-filtered', [MusicScoreController::class, 'getListFiltered']);
    Route::get('/allmusic', [MusicScoreController::class, 'allmusic']);
    Route::get('/tempallmusic', [MusicScoreController::class, 'tempallmusic']);
    Route::get('/statistics/{id}', [MusicScoreController::class, 'getStatistics']);
});

// Endpoint público para logging de archivos personales
Route::post('/analytics/log-personal-file-view', [MusicScoreController::class, 'logPersonalFileView']);

Route::middleware(['auth:sanctum', 'check_active'])->group(function () {

    // Auth section
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logoutUser']);
        Route::post('/token/refresh', [AuthController::class, 'refreshToken']);
        Route::prefix('user')->group(function () {
            Route::get('/get/{get}', [AuthController::class, 'getUser']);
            Route::post('/edit/{id}', [AuthController::class, 'updateUser']);
            Route::delete('/delete/{id}', [AuthController::class, 'delete']);
            Route::post('/ask-composer', [AuthController::class, 'askForBeComposer']); //falta + TODO: falta aprobar desde algún lado el rol
            Route::get('/', function (Request $request) {
                return $request->user()->load('roles.permissions', 'permissions');
            });
            Route::get('/check-user', [AuthController::class, 'getUser']);
            Route::get('/check-composer', [AuthController::class, 'checkComposer']);
            Route::get('/check-subscription', [AuthController::class, 'checkSubscription']);
        });
    });

    // Composer section
    Route::prefix('composer')->group(function () {
        // Route::middleware(['permission:suggest_composer'])->post('/suggest',[ComposerController::class,'sugest']);
        Route::post('/create', [ComposerController::class, 'create']);
        Route::post('/update/{id}', [ComposerController::class, 'update']);
        Route::delete('/delete/{id}', [ComposerController::class, 'delete']);
    });

    // Instruments section
    Route::prefix('instruments')->group(function () {
        Route::post('/suggest', [InstrumentController::class, 'sugest']);
        Route::prefix('family')->group(function () {
            Route::post('/suggest', [FamilyInstrumentController::class, 'sugest']);
        });
        Route::post('/create', [InstrumentController::class, 'create']);
    });


    // Style-music section
    Route::prefix('style-music')->group(function () {
        Route::post('/suggest', [StyleMusicController::class, 'sugest']);
    });


    //investigando: https://wasabi-support.zendesk.com/hc/en-us/articles/360035684991-How-do-I-use-Laravel-with-Wasabi-
    //investigando: https://www.itsolutionstuff.com/post/how-to-add-password-protection-for-pdf-file-in-laravelexample.html
    // Music-store section
    Route::prefix('music-score')->group(function () {
        Route::post('/create', [MusicScoreController::class, 'create']);
        Route::get('/fav-music-score', [MusicScoreController::class, 'favMusicScore']);
        Route::get('/remove-fav-music-score', [MusicScoreController::class, 'removeFavMusicScore']);
        Route::get('/user-fav-music-score', [MusicScoreController::class, 'usersFavMusicScore']);

        Route::get('/getMusicScorePdf/{id}', [MusicScoreController::class, 'getAllPdf']);
        Route::post('/getPdfContent', [MusicScoreController::class, 'getPdfContent']);
        Route::get('/composer', [MusicScoreController::class, 'composerMusic']);
        Route::post('/update/{id}', [MusicScoreController::class, 'update']);
        Route::delete('/delete/{id}', [MusicScoreController::class, 'delete']);
    });

    //CRUD de ComposerRequest
    // Composer-request section
    Route::prefix('composer-request')->group(function () {
        Route::get('/list', [ComposerRequestController::class, 'getList']);
        Route::get('/get/{get}', [ComposerRequestController::class, 'get']);
        Route::post('/create', [ComposerRequestController::class, 'create']);
        Route::post('/update/{id}', [ComposerRequestController::class, 'update']);
        Route::delete('/delete/{id}', [ComposerRequestController::class, 'delete']);

        Route::post('/update-status/{id}', [ComposerRequestController::class, 'update_status']);

        // Route::middleware(['permission:edit_composer_request'])->post('/edit',[ComposerRequestController::class,'edit']);
        // Route::middleware(['permission:delete_composer_request'])->post('/delete',[ComposerRequestController::class,'delete']);
        // Route::middleware(['permission:approve_composer_request'])->post('/approve',[ComposerRequestController::class,'approve']);
        // Route::middleware(['permission:reject_composer_request'])->post('/reject',[ComposerRequestController::class,'reject']);
    });

    // Subscription
    Route::prefix('subscription')->group(function () {
        Route::post('/subscribed-user', [SubscriptionController::class, 'subscribed_user'])->name('subscribed_user');
        Route::get('/subscription-plans', [SubscriptionController::class, 'subscription_plans_list'])->name('subscription_plans_list');
        Route::post('/subscription-payment', [SubscriptionController::class, 'subscription_payment'])->name('subscription.payment');
        Route::get('/subscription-status', [SubscriptionController::class, 'subscription_status'])->name('subscription.status');
        Route::post('paypal-webhook', [App\Http\Controllers\admin\SubscriptionController::class, 'paypal_webhook'])->name('paypalWebhook');
    });

    // InAppSubscription
    Route::prefix('inapp-subscription')->group(function () {
        Route::post('/sync-subscribe', [InAppSubscriptionController::class, 'syncSubscribe'])->name('inapp-subscribe');
        Route::post('/apply-premium-trial', [InAppSubscriptionController::class, 'applyPremiumTrail'])->name('inapp-apply-premium-trial');
    });

    // User ensemble status (premium logic)
    Route::get('/user/ensemble-status', [EnsembleController::class, 'ensembleStatus']);

    // My ensembles
    Route::get('/my-ensembles', [EnsembleController::class, 'myEnsembles']);

    // Ensembles (admin only for create/update/delete)
    Route::prefix('ensembles')->group(function () {
        Route::get('/', [EnsembleController::class, 'index']);
        Route::post('/', [EnsembleController::class, 'store']);
        Route::get('/{ensemble}', [EnsembleController::class, 'show']);
        Route::put('/{ensemble}', [EnsembleController::class, 'update']);
        Route::delete('/{ensemble}', [EnsembleController::class, 'destroy']);

        // Members
        Route::get('/{ensemble}/members', [EnsembleController::class, 'members']);
        Route::post('/{ensemble}/members', [EnsembleController::class, 'addMember']);
        Route::put('/{ensemble}/members/{user}', [EnsembleController::class, 'updateMember']);
        Route::delete('/{ensemble}/members/{user}', [EnsembleController::class, 'removeMember']);

        // Folders
        Route::get('/{ensemble}/folders', [EnsembleController::class, 'folders']);
        Route::post('/{ensemble}/folders', [EnsembleController::class, 'storeFolder']);
        Route::put('/folders/{folder}', [EnsembleController::class, 'updateFolder']);
        Route::delete('/folders/{folder}', [EnsembleController::class, 'destroyFolder']);

        // Scores
        Route::get('/{ensemble}/scores', [EnsembleController::class, 'scores']);
        Route::post('/{ensemble}/scores', [EnsembleController::class, 'storeScore']);

        // Rehearsals
        Route::get('/{ensemble}/rehearsals', [EnsembleController::class, 'rehearsals']);
        Route::post('/{ensemble}/rehearsals', [EnsembleController::class, 'storeRehearsal']);
        Route::put('/rehearsals/{rehearsal}', [EnsembleController::class, 'updateRehearsal']);
        Route::delete('/rehearsals/{rehearsal}', [EnsembleController::class, 'destroyRehearsal']);
    });
});
