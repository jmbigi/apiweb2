<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Genera un sitemap para el sitio web';

    public function handle()
    {
        $sitemap = Sitemap::create();

        // Agrega tus rutas aquí
        $sitemap->add(Url::create('/')->setPriority(1.0));
        $sitemap->add(Url::create('/about')->setPriority(0.8));
        // Agrega más rutas según sea necesario

        $sitemap->writeToFile(public_path('sitemap.xml'));
        $this->info('Sitemap generado: public/sitemap.xml');
    }
}
