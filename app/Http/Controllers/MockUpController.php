<?php

namespace App\Http\Controllers;

use App\Models\MusicScore;
use Illuminate\Http\Request;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\Browsershot\Browsershot;

class MockUpController extends Controller
{


    public function showPage(Request $request, string $locale, string $name)
    {

        // Buscar el MusicScore por su nombre
        $musicScore = MusicScore::with(['instruments', 'style_musics'])->where('name', $name)->first();

        if (!$musicScore) {
            return abort(404);
        }

        // Pasar el MusicScore a la vista
        return view('music_scores.show-image', compact('locale', 'musicScore'));
    }



    public function generatePdf(Request $request, string $locale, string $name)
    {

        $chrome_path = "/usr/bin/google-chrome";
        // Funciona: Browsershot::url('https://example.com')->setChromePath($chrome_path)->noSandBox()->save('example.pdf');
        // Recuerda: chown -R www-data:www-data /var/www/web.faristol.net/public/cache

        // Buscar el MusicScore por su nombre
        $musicScore = MusicScore::with(['instruments', 'style_musics'])->where('name', $name)->first();

        if (!$musicScore) {
            return abort(404);
        }

        // Renderiza la vista y genera el PDF
        $pdf = Pdf::view('music_scores.show-image', compact('locale', 'musicScore'));

        // Guarda el PDF en caché o descárgalo directamente
        $filePath = public_path("cache/music_score_{$locale}_{$musicScore->id}.pdf");
        if (!file_exists(public_path('cache'))) {
            mkdir(public_path('cache'), 0755, true);
        }
        //die($filePath);
        $pdf->withBrowsershot(function (Browsershot $browsershot) use ($chrome_path) {
            $browsershot->setChromePath($chrome_path);
        })->save($filePath);

        return response()->download($filePath);
    }

    public static function genImageLocaleAndName(string $locale, string $name) {
        $chrome_path = "/usr/bin/google-chrome";
        // Funciona: Browsershot::url('https://example.com')->setChromePath($chrome_path)->noSandBox()->save('example.pdf');
        // Recuerda: chown -R www-data:www-data /var/www/web.faristol.net/public/cache

        // Buscar el MusicScore por su nombre
        $musicScore = MusicScore::with(['instruments', 'style_musics'])->where('name', $name)->first();

        if (!$musicScore) {
            return '';
        }

        $pdfFilePath = public_path("cache/music_score_{$locale}_{$musicScore->id}.pdf");
        $jpgFilePath = public_path("cache/music_score_{$locale}_{$musicScore->id}.jpg");

        if (!file_exists($pdfFilePath)) {
            // Renderiza la vista y genera el PDF
            $pdf = Pdf::view('music_scores.show-image', compact('locale', 'musicScore'));

            // Guarda el PDF en caché
            if (!file_exists(public_path('cache'))) {
                mkdir(public_path('cache'), 0755, true);
            }
            $pdf->withBrowsershot(function (Browsershot $browsershot) use ($chrome_path) {
                $browsershot->setChromePath($chrome_path);
            })->save($pdfFilePath);
        }

        if (!file_exists($jpgFilePath)) {
            // Convertir PDF a jpg
            $pdf = new \Spatie\PdfToImage\Pdf($pdfFilePath);
            $pdf->saveImage($jpgFilePath);
        }

        return $jpgFilePath;

    }

    public function generateImage(Request $request, string $locale, string $name)
    {
        $jpgFilePath = MockUpController::genImageLocaleAndName($locale, $name);

        if ($jpgFilePath == '') {
            return abort(404);
        } 

        // Obtener el contenido de la imagen jpg
        $imageData = file_get_contents($jpgFilePath);

        // Retornar el contenido de la imagen con el encabezado adecuado
        return response($imageData, 200)
            ->header('Content-Type', 'image/jpeg')
            ->header('Cache-Control', 'public')
            ->header('Expires', gmdate('D, d M Y H:i:s', time() + 60) . ' GMT'); // Expira en 1 minuto            
        //->header('Cache-Control', 'max-age=60, public'); // Cache de 1 minuto (60 segundos);
    }
}
