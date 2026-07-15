<?php

namespace App\Exports;

use App\Models\LogDisplayPersonalScore;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PersonalLogsExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return LogDisplayPersonalScore::with('user:id,name,email')
            ->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Usuario ID',
            'Nombre Usuario',
            'Email Usuario',
            'Archivo',
            'Fecha Acceso',
            'Hora Acceso',
            'Tipo Usuario'
        ];
    }

    public function map($log): array
    {
        return [
            $log->id,
            $log->user_id ?? 'Anónimo',
            $log->user ? $log->user->name : 'Usuario Anónimo',
            $log->user ? $log->user->email : 'N/A',
            $log->filename,
            $log->created_at->format('Y-m-d'),
            $log->created_at->format('H:i:s'),
            $log->user_id ? 'Registrado' : 'Anónimo'
        ];
    }
}
