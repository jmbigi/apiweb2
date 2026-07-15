<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsoExport implements FromQuery, WithHeadings
{
    public function query()
    {
        return DB::table('log_view_music_score_details as l')
            ->leftJoin('users as u', 'l.user_id', '=', 'u.id')
            ->leftJoin('music_scores as ms', 'l.music_score_id', '=', 'ms.id')
            ->select([
                'l.user_id',
                'u.email as user_email',
                DB::raw('DATE(l.created_at) as fecha'),
                DB::raw('COUNT(*) as visitas'),
                'ms.name as partitura'
            ])
            ->groupBy(
                'l.user_id',
                'u.email',
                DB::raw('DATE(l.created_at)'),
                'l.music_score_id',
                'ms.name'
            )
            ->orderBy('user_email', 'asc');
    }

    public function headings(): array
    {
        return [
            'ID Usuario',
            'Email',
            'Fecha',
            'Visitas',
            'Partitura'
        ];
    }
}
