<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\MusicScore;
use App\Services\LocationService;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Genera un sitemap para el sitio web';

    public function handle()
    {
        // Idiomas soportados
        $supportedLangs = LocationService::VALID_LANGUAGES;

        $sitemap = Sitemap::create();

        $url = Url::create(url('/'))->setPriority(1.0);
        $sitemap->add($url);
        $this->info($url->url);

        // Agregar URLs con idiomas
        foreach ($supportedLangs as $lang) {
            $url = Url::create(url("/{$lang}"))->setPriority(0.9);
            $sitemap->add($url);
            $this->info($url->url);
        }

        $url = Url::create(url("/sitemap"))->setPriority(0.9);
        $sitemap->add($url);
        $this->info($url->url);

        // Agregar URLs con idiomas
        foreach ($supportedLangs as $lang) {
            $url = Url::create(url("/lang/{$lang}"))->setPriority(0.9);
            $sitemap->add($url);
            $this->info($url->url);
        }

        // Agregar Sitemaps con idiomas
        foreach ($supportedLangs as $lang) {
            $url = Url::create(url("/sitemap/{$lang}"))->setPriority(0.9);
            $sitemap->add($url);
            $this->info($url->url);
        }

        // Obtener todas las partituras y cargar relaciones de estilos e instrumentos
        $musicScores = MusicScore::with(['style_musics', 'instruments'])->cursor();

        foreach ($musicScores as $musicScore) {
            // Generar URL de la partitura
            $url = Url::create(route('score-viewbyname', ['name' => rawurlencode($musicScore->name)]))
                ->setPriority(0.8)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY);
            $sitemap->add($url);
            $this->info($url->url);

            // Iterar sobre los estilos de cada partitura
            foreach ($musicScore->style_musics as $style) {
                $styleUrl = Url::create(route('score-viewbystyleandscorename', ['stylename' => $style->name, 'scorename' => rawurlencode($musicScore->name)]))
                    ->setPriority(0.8)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY);
                $sitemap->add($styleUrl);
                $this->info($styleUrl->url);
            }

            // Iterar sobre los instrumentos de cada partitura
            foreach ($musicScore->instruments as $instrument) {
                $instrumentUrl = Url::create(route('score-viewbyinstrumentandscorename', ['instrumentname' => $instrument->name, 'scorename' => rawurlencode($musicScore->name)]))
                    ->setPriority(0.6)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY);
                $sitemap->add($instrumentUrl);
                $this->info($instrumentUrl->url);
            }
        }

        // Guardar el sitemap en la carpeta public
        $sitemap->writeToFile(public_path('sitemap.xml'));
    }
}
