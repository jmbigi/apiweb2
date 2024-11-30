<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\SiteStatistic;

class LogSiteStatistics
{
    public function handle($request, Closure $next)
    {
        // Obtén la URL completa del sitio
        $siteUrl = url('/'); // Esto devuelve la URL base del sitio (por ejemplo, 'https://tusitio.com')

        // Obtén la ruta actual de la página visitada
        $page = $request->path();

        // Concatenar la URL completa con la ruta de la página (por ejemplo, 'https://tusitio.com/home')
        $fullUrl = $siteUrl . ($page == '/' ? '' : '/') . $page;

        // Busca la estadística o crea una nueva
        SiteStatistic::updateOrCreate(
            ['page' => $fullUrl],
            ['views' => \DB::raw('views + 1')]
        );

        return $next($request);
    }
}
