<?php

namespace App\Exports;

use App\Models\Activity;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class CollaborativeActivitiesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected $activities;

    public function __construct($activities)
    {
        $this->activities = $activities;
    }

    public function collection()
    {
        // Agrupar las actividades por tipo y número de referencia
        $groupedActivities = $this->activities->groupBy(function ($activity) {
            return $activity->tipo . ' - ' . $activity->numero_referencia;
        });

        $exportData = collect();

        foreach ($groupedActivities as $groupKey => $activitiesInGroup) {
            // Agregar encabezado del grupo
            $exportData->push((object)[
                'is_group_header' => true,
                'group_name' => $groupKey,
                'total_activities' => $activitiesInGroup->count(),
                'total_participants' => $activitiesInGroup->pluck('user_id')->unique()->count(),
                'total_time' => $activitiesInGroup->sum('tiempo'),
                'date_range' => $activitiesInGroup->min('fecha_actividad') . ' - ' . $activitiesInGroup->max('fecha_actividad')
            ]);

            // Agregar actividades del grupo
            foreach ($activitiesInGroup->sortBy('fecha_actividad') as $activity) {
                $exportData->push($activity);
            }

            // Agregar fila vacía para separar grupos
            $exportData->push((object)['is_separator' => true]);
        }

        return $exportData;
    }

    public function headings(): array
    {
        return [
            'Grupo/Fecha',
            'Empleado',
            'Coordinación',
            'Dirección',
            'Título',
            'Tipo',
            'Número de Referencia',
            'Tiempo (Horas)',
            'Observaciones',
            'Estadísticas'
        ];
    }

    public function map($row): array
    {
        if (isset($row->is_group_header) && $row->is_group_header) {
            return [
                'GRUPO: ' . $row->group_name,
                '',
                '',
                '',
                '',
                '',
                '',
                $row->total_time . ' hrs',
                '',
                'Actividades: ' . $row->total_activities . ' | Participantes: ' . $row->total_participants . ' | Período: ' . $row->date_range
            ];
        }

        if (isset($row->is_separator) && $row->is_separator) {
            return ['', '', '', '', '', '', '', '', '', ''];
        }

        return [
            $row->fecha_actividad,
            $row->user->name,
            $row->user->coordinacion ?? 'No especificada',
            $row->user->direccion ?? 'No especificada',
            $row->titulo,
            $row->tipo,
            $row->numero_referencia,
            $row->tiempo,
            $row->observaciones,
            ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [
            // Encabezados principales en negrita
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];

        // Aplicar estilos a las filas de encabezado de grupo
        $rowIndex = 2; // Comenzar después del encabezado
        foreach ($this->collection() as $row) {
            if (isset($row->is_group_header) && $row->is_group_header) {
                $styles[$rowIndex] = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '4472C4']]
                ];
            }
            $rowIndex++;
        }

        return $styles;
    }

    public function title(): string
    {
        return 'Reporte Colaborativo';
    }
}
