<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersSuscritosExport implements FromQuery, WithHeadings
{
    public function query()
    {
        return DB::table('view_users_suscritos')
            ->select('email', 'fecha_inscripcion', 'plan_pago', 'prueba_premium', 'estado_plan', 'subscription_end_date')
            ->orderBy('fecha_inscripcion', 'desc'); // Añade esta línea
    }

    public function headings(): array
    {
        return [
            'Email',
            'Fecha de Inscripción',
            'Plan de Pago',
            'Prueba Premium',
            'Estado del Plan',
            'Fecha Fin de Suscripción'
        ];
    }
}
