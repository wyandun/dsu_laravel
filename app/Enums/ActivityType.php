<?php

namespace App\Enums;

enum ActivityType: string
{
    case HELPDESK = 'Helpdesk';
    case REUNION = 'Reunión';
    case QUIPUX = 'Quipux';
    case CTIT = 'CTIT';
    case SGA = 'SGA';
    case CORREO = 'Correo';
    case CONTRATO = 'Contrato';
    case OFICIO = 'Oficio';
    case GPR = 'GPR';
    case OTROS = 'Otros';

    /**
     * Obtener todos los valores del enum como array
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Obtener todos los casos como array asociativo (value => label)
     */
    public static function toSelectArray(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->value] = $case->value;
        }
        return $result;
    }

    /**
     * Obtener el color CSS asociado al tipo para la UI
     */
    public function getColor(): string
    {
        return match($this) {
            self::HELPDESK => 'orange',
            self::REUNION => 'purple',
            self::QUIPUX => 'blue',
            self::CTIT => 'green',
            self::SGA => 'teal',
            self::CORREO => 'yellow',
            self::CONTRATO => 'red',
            self::OFICIO => 'indigo',
            self::GPR => 'pink',
            self::OTROS => 'gray',
        };
    }

    /**
     * Obtener la descripción del tipo
     */
    public function getDescription(): string
    {
        return match($this) {
            self::HELPDESK => 'Soporte técnico y mesa de ayuda',
            self::REUNION => 'Reuniones de trabajo y coordinación',
            self::QUIPUX => 'Sistema de gestión documental Quipux',
            self::CTIT => 'Actividades del Centro de Tecnologías de Información y Telecomunicaciones',
            self::SGA => 'Sistema de Gestión Administrativa',
            self::CORREO => 'Gestión de correos electrónicos',
            self::CONTRATO => 'Gestión y seguimiento de contratos',
            self::OFICIO => 'Elaboración y gestión de oficios',
            self::GPR => 'Gestión por Resultados',
            self::OTROS => 'Otras actividades no categorizadas',
        };
    }

    /**
     * Verificar si un valor es válido
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::toArray());
    }
}
