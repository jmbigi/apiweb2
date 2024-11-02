<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

use App\Models\MusicScore; // Asegúrate de que la clase esté importada

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Genera un sitemap para el sitio web';

    public function handle()
    {
        $sitemap = Sitemap::create();
        $sitemap->add(Url::create('/')->setPriority(1.0));

        // Obtener todas las partituras y añadir cada una al sitemap
        $musicScores = MusicScore::all();

        foreach ($musicScores as $musicScore) {
            $url = Url::create(route('score-viewbyname', ['name' => $musicScore->name]))
                ->setPriority(0.8) // Puedes ajustar el valor de prioridad según la relevancia
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY);

            $sitemap->add($url);
            // Imprimir la URL en la consola
            $this->info($url->url);
        }

        // Guardar el sitemap en la carpeta public
        $sitemap->writeToFile(public_path('sitemap.xml'));
    }
}
