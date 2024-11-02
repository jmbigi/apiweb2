<?php

namespace App\Http\Controllers;

use App\Models\MusicScore; // Asegúrate de tener el modelo correcto
use Illuminate\Http\Request;

class MusicScoreController extends Controller
{
    public function showByName(Request $request, string $name)
    {
        // Buscar el MusicScore por su nombre
        $musicScore = MusicScore::where('name', $name)->first();

        // Verificar si se encontró el MusicScore
        if (!$musicScore) {
            return redirect()->route('home'); // Redirigir a la ruta nombrada 'home'
        }

        // Pasar el MusicScore a la vista
        return view('music_scores.show', compact('musicScore'));
    }
}
