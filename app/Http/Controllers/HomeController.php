<?php

namespace App\Http\Controllers;

use App\Models\MusicScore; // Asegúrate de tener el modelo correcto
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Obtiene la fecha de hace un mes
        $oneMonthAgo = Carbon::now()->subMonth();

        // Consulta para obtener el ID del musicScore más visitado en el último mes
        $musicScoreId = DB::table('log_display_music_scores')
            ->select('music_scores_id', DB::raw('count(*) as visit_count'))
            ->where('created_at', '>=', $oneMonthAgo) // Filtra visitas del último mes
            ->groupBy('music_scores_id')
            ->orderBy('visit_count', 'desc')
            ->limit(1)
            ->pluck('music_scores_id')
            ->first();

        // Si se encontró un musicScore más visitado, obtener el objeto correspondiente
        if ($musicScoreId) {
            $musicScore = MusicScore::find($musicScoreId);
        } else {
            // Obtener el musicScore más reciente si no hay visitas registradas
            $musicScore = MusicScore::latest()->first(); // Cambiado para obtener el más reciente
        }

        // Verificar si se encontró el MusicScore
        if (!$musicScore) {
            return abort(404); // O puedes redirigir a otra página
        }

        // Pasar el MusicScore a la vista
        return view('music_scores.show', compact('musicScore'));
    }
}
