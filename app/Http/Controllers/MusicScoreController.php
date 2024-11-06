<?php

namespace App\Http\Controllers;

use App\Models\MusicScore; // Asegúrate de tener el modelo correcto
use Illuminate\Http\Request;

class MusicScoreController extends Controller
{
    public function showByName(Request $request, string $name)
    {
        // dd($name);
        // Buscar el MusicScore por su nombre
        $musicScore = MusicScore::with(['instruments', 'style_musics'])->where('name', $name)->first();

        // Verificar si se encontró el MusicScore
        if (!$musicScore) {
            return redirect()->route('home'); // Redirigir a la ruta nombrada 'home'
        }

        // Pasar el MusicScore a la vista
        return view('music_scores.show', compact('musicScore'));
    }

    public function showByStyleAndScoreName(Request $request, string $stylename, string $scorename)
    {
        // dd($stylename, $scorename);
        // Buscar el MusicScore por su nombre y estilo
        $musicScore = MusicScore::with(['instruments', 'style_musics'])
            ->where('name', $scorename)
            //->whereHas('style_musics', function ($query) use ($stylename) {
            //    $query->where('name', $stylename);
            //})
            ->first();

        // Verificar si se encontró el MusicScore
        if (!$musicScore) {
            return redirect()->route('home'); // Redirigir a la ruta nombrada 'home'
        }

        // Pasar el MusicScore a la vista
        return view('music_scores.show', compact('musicScore'));
    }

    public function showByInstrumentAndScoreName(Request $request, string $instrumentname, string $scorename)
    {
        // dd($instrumentname, $scorename);
        // Buscar el MusicScore por su nombre e instrumento
        $musicScore = MusicScore::with(['instruments', 'style_musics'])
            ->where('name', $scorename)
            ->whereHas('instruments', function ($query) use ($instrumentname) {
                $query->where('name', $instrumentname);
            })
            ->first();

        // Verificar si se encontró el MusicScore
        if (!$musicScore) {
            return redirect()->route('home'); // Redirigir a la ruta nombrada 'home'
        }

        // Pasar el MusicScore a la vista
        return view('music_scores.show', compact('musicScore'));
    }
}
