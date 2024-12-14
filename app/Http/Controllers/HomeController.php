<?php

namespace App\Http\Controllers;

use App\Models\MusicScore; // Asegúrate de tener el modelo correcto
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Services\LocationService;
use Illuminate\Support\Facades\Cookie;

class HomeController extends Controller
{
    protected $locationService;

    /**
     * Constructor del controlador.
     *
     * @param LocationService $locationService
     */
    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function index(Request $request, $lang = null)
    {
        if ($lang && $this->locationService->isValidLanguage($lang)) {
            $locale = $lang;
            Cookie::queue(Cookie::make('preferredLang', $locale, 60 * 24 * 7, null, null, false, true)); // Cifrada
        } else {
            // Obtener el idioma
            $locale = $this->locationService->getLocale($request);
        }

        // Establecer el locale de la aplicación
        App::setLocale($locale);

        // Obtiene la fecha de hace un mes
        $oneMonthAgo = Carbon::now()->subMonth();

        // Consulta para obtener el ID del musicScore más visitado en el último mes
        $musicScoreId = DB::table('log_display_music_scores')
            ->select('music_scores_id', DB::raw('count(*) as visit_count'))
            ->where('created_at', '>=', $oneMonthAgo) // Filtra visitas del último mes
            ->groupBy('music_scores_id')
            ->orderBy('visit_count', 'desc')
            ->limit(1)
            ->pluck('music_scores_id')
            ->first();

        // Si se encontró un musicScore más visitado, obtener el objeto correspondiente
        if ($musicScoreId) {
            $musicScore = MusicScore::with(['instruments', 'style_musics'])->find($musicScoreId);
        } else {
            // Obtener el musicScore más reciente si no hay visitas registradas
            $musicScore = MusicScore::with(['instruments', 'style_musics'])->latest()->first(); // Cambiado para obtener el más reciente
        }

        // Verificar si se encontró el MusicScore
        if (!$musicScore) {
            return abort(404); // O puedes redirigir a otra página
        }

        $styleName = null;
        $instrumentName = null;
        // Pasar el MusicScore a la vista
        return view('music_scores.show', compact('locale', 'musicScore', 'styleName', 'instrumentName'));
    }
}
