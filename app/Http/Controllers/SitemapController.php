<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LocationService;
use App\Models\MusicScore; // Asegúrate de tener el modelo correcto
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;

class SitemapController extends Controller
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
        $defaultLang = true;
        if ($lang && $this->locationService->isValidLanguage($lang)) {
            $defaultLang = false;
            $locale = $lang;
            Cookie::queue(Cookie::make('preferredLang', $locale, 60 * 24 * 7, null, null, false, true)); // Cifrada
        } else {
            // Obtener el idioma
            $locale = $this->locationService->getLocale($request);
        }

        // Establecer el locale de la aplicación
        App::setLocale($locale);

        return view('sitemap', compact('locale', 'defaultLang'));
    }
}
