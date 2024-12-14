<?php

namespace App\Http\Controllers;

use App\Models\MusicScore; // Asegúrate de tener el modelo correcto
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Services\LocationService;

class MusicScoreController extends Controller
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

    public function showByName(Request $request, string $name, ?string $lang = null)
    {
        if ($lang && $this->locationService->isValidLanguage($lang)) {
            $locale = $lang;
        } else {
            // Obtener el idioma
            $locale = $this->locationService->getLocale($request);
        }

        // Establecer el locale de la aplicación
        App::setLocale($locale);

        // Buscar el MusicScore por su nombre
        $musicScore = MusicScore::with(['instruments', 'style_musics'])->where('name', $name)->first();

        // Verificar si se encontró el MusicScore
        if (!$musicScore) {
            return redirect()->route('home'); // Redirigir a la ruta nombrada 'home'
        }

        $styleName = null;
        $instrumentName = null;
        // Pasar el MusicScore a la vista
        return view('music_scores.show', compact('locale', 'musicScore', 'styleName', 'instrumentName'));
    }

    public function showByLangAndName(Request $request, string $lang, string $name)
    {
        return $this->showByName($request, $name, $lang);
    }


    public function showByStyleAndScoreName(Request $request, string $style_name, string $scorename, ?string $lang = null)
    {
        if ($lang && $this->locationService->isValidLanguage($lang)) {
            $locale = $lang;
        } else {
            // Obtener el idioma
            $locale = $this->locationService->getLocale($request);
        }

        // Establecer el locale de la aplicación
        App::setLocale($locale);

        // Buscar el MusicScore por su nombre y estilo
        $musicScore = MusicScore::with(['instruments', 'style_musics'])
            ->where('name', $scorename)
            ->whereHas('style_musics', function ($query) use ($style_name) {
                $query->where('name', $style_name);
            })
            ->first();

        // Verificar si se encontró el MusicScore
        if (!$musicScore) {
            return redirect()->route('home'); // Redirigir a la ruta nombrada 'home'
        }

        $styleName = $style_name;
        $instrumentName = null;
        // Pasar el MusicScore a la vista
        return view('music_scores.show', compact('locale', 'musicScore', 'styleName', 'instrumentName'));
    }

    public function showByLangAndStyleAndScoreName(Request $request, string $lang, string $style_name, string $scorename)
    {
        return $this->showByStyleAndScoreName($request, $style_name, $scorename, $lang);
    }

    public function showByInstrumentAndScoreName(Request $request, string $instrument_name, string $scorename, ?string $lang = null)
    {
        if ($lang && $this->locationService->isValidLanguage($lang)) {
            $locale = $lang;
        } else {
            // Obtener el idioma
            $locale = $this->locationService->getLocale($request);
        }

        // Establecer el locale de la aplicación
        App::setLocale($locale);

        // Buscar el MusicScore por su nombre e instrumento
        $musicScore = MusicScore::with(['instruments', 'style_musics'])
            ->where('name', $scorename)
            ->whereHas('instruments', function ($query) use ($instrument_name) {
                $query->where('name', $instrument_name);
            })
            ->first();

        // Verificar si se encontró el MusicScore
        if (!$musicScore) {
            return redirect()->route('home'); // Redirigir a la ruta nombrada 'home'
        }

        $styleName = null;
        $instrumentName = $instrument_name;
        // Pasar el MusicScore a la vista
        return view('music_scores.show', compact('locale', 'musicScore', 'styleName', 'instrumentName'));
    }

    public function showByLangAndInstrumentAndScoreName(Request $request, string $lang, string $instrument_name, string $scorename)
    {
        return $this->showByInstrumentAndScoreName($request, $instrument_name, $scorename, $lang);
    }
}
