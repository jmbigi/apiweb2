<?php

namespace App\Http\Controllers;

use App\Models\MusicScore;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Barryvdh\DomPDF\Facade\Pdf; // Para generar PDFs
use Spatie\Browsershot\Browsershot; // Para generar imágenes

class MockUpController extends Controller
{
    public function generatePdf(Request $request, string $locale, string $name)
    {
        // Buscar el MusicScore por su nombre
        $musicScore = MusicScore::with(['instruments', 'style_musics'])->where('name', $name)->first();

        if (!$musicScore) {
            return abort(404);
        }

        // Renderiza la vista y genera el PDF
        $pdf = Pdf::loadView('music_scores.show-image', compact('locale', 'musicScore'));

        // Guarda el PDF en caché o descárgalo directamente
        $filePath = public_path("cache/music_score_{$musicScore->id}.pdf");
        if (!file_exists(public_path('cache'))) {
            mkdir(public_path('cache'), 0755, true);
        }
        //die($filePath);
        $pdf->save($filePath);

        return response()->download($filePath);
    }

    public function generateImage(Request $request, string $locale, string $name)
    {

        // Buscar el MusicScore por su nombre
        $musicScore = MusicScore::with(['instruments', 'style_musics'])->where('name', $name)->first();

        if (!$musicScore) {
            return abort(404);
        }

        // Generar la imagen desde la vista
        $html = view('music_scores.show-image', compact('locale', 'musicScore'))->render();

        $filePath = public_path("cache/music_score_{$musicScore->id}.png");

        return response()->download($filePath);
    }
}
