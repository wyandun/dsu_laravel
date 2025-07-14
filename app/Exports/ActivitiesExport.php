<?php

namespace App\Exports;

use App\Models\Activity;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActivitiesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $activities;

    public function __construct($activities)
    {
        $this->activities = $activities;
    }

    public function collection()
    {
        return $this->activities;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Empleado',
            'Coordinación',
            'Dirección',
            'Título',
            'Tipo',
            'Número de Referencia',
            'Tiempo (Horas)',
            'Observaciones'
        ];
    }

    public function map($activity): array
    {
        return [
            $activity->fecha_actividad->format('Y-m-d'),
            $activity->user->name,
            $activity->user->direccion ? $activity->user->direccion->coordinacion->nombre : 'No especificada',
            $activity->user->direccion ? $activity->user->direccion->nombre : 'No especificada',
            $activity->titulo,
            $activity->tipo,
            $activity->numero_referencia,
            $activity->tiempo,
            $activity->observaciones
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }
}
