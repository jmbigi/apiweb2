<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\MusicScore;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Genera un sitemap para el sitio web';

    public function handle()
    {
        $sitemap = Sitemap::create();
        $sitemap->add(Url::create('/')->setPriority(1.0));

        // Obtener todas las partituras y cargar relaciones de estilos e instrumentos
        $musicScores = MusicScore::with(['style_musics', 'instruments'])->cursor();

        foreach ($musicScores as $musicScore) {
            // Generar URL de la partitura
            $url = Url::create(route('score-viewbyname', ['name' => $musicScore->name]))
                ->setPriority(0.8)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY);
            $sitemap->add($url);
            $this->info($url->url);

            // Iterar sobre los estilos de cada partitura
            foreach ($musicScore->style_musics as $style) {
                $styleUrl = Url::create(route('score-viewbystyleandscorename', ['stylename' => $style->name, 'scorename' => $musicScore->name]))
                    ->setPriority(0.8)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY);
                $sitemap->add($styleUrl);
                $this->info($styleUrl->url);
            }

            // Iterar sobre los instrumentos de cada partitura
            foreach ($musicScore->instruments as $instrument) {
                $instrumentUrl = Url::create(route('score-viewbyinstrumentandscorename', ['instrumentname' => $instrument->name, 'scorename' => $musicScore->name]))
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
