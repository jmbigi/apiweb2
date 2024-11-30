<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SiteStatistic;
use Carbon\Carbon;

class SiteStatisticsController extends Controller
{
    public function index(Request $request)
    {
        // Obtén las fechas del filtro (si las hay)
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Si no se han proporcionado fechas, establece el rango por defecto (últimos 7 días)
        if (!$startDate || !$endDate) {
            $startDate = now()->subWeek()->toDateString();  // 7 días atrás
            $endDate = now()->toDateString();  // Hoy
        }

        $endDateEOD = $endDate;
        // Asegúrate de que el endDate tenga la hora actual (por defecto, a las 23:59:59)
        if ($endDate) {
            $endDateEOD = Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay();  // Asegura que sea hasta las 23:59:59 del día
        }

        if ($startDate && $endDateEOD && strtotime($startDate) > strtotime($endDateEOD)) {
            return back()->withErrors(['Las fechas son inválidas.']);
        }

        // Consulta básica
        $query = SiteStatistic::query();

        // Filtra por fechas si están presentes
        if ($startDate && $endDateEOD) {
            $query->whereBetween('created_at', [$startDate, $endDateEOD]);
        }

        $excludedPages = [
            //            'https://web.faristol.net/',
        ];

        // Excluir páginas
        $query->whereNotIn('page', $excludedPages);

        // Excluir páginas que terminen con 'dashboard'
        $query->where('page', 'not like', '%dashboard');

        // Excluir páginas que terminen con 'stats'
        $query->where('page', 'not like', '%/stats');

        // Excluir páginas que terminen con 'login'
        $query->where('page', 'not like', '%/login');

        // Ordena por vistas y obtiene los resultados
        $statistics = $query->orderBy('views', 'desc')->limit(10)->get();

        // Limpiar las URLs y quitar el dominio
        $statistics = $statistics->map(function ($stat) {
            // Decodificar la URL primero
            $stat->page = urldecode($stat->page);

            // Eliminar el dominio de la URL
            $parsedUrl = parse_url($stat->page);
            if (isset($parsedUrl['path'])) {
                $stat->page = $parsedUrl['path']; // Solo toma el path
            }

            return $stat;
        });

        return view('stats', compact('statistics', 'startDate', 'endDate'));
    }
}
