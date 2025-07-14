<?php

namespace App\Enums;

enum ActivityType: string
{
    case QUIPUX = 'Quipux';
    case MANTIS = 'Mantis';
    case CTIT = 'CTIT';
    case CORREO = 'Correo';
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
            self::QUIPUX => 'blue',
            self::MANTIS => 'red',
            self::CTIT => 'green',
            self::CORREO => 'yellow',
            self::OTROS => 'gray',
        };
    }

    /**
     * Obtener la descripción del tipo
     */
    public function getDescription(): string
    {
        return match($this) {
            self::QUIPUX => 'Sistema de gestión documental Quipux',
            self::MANTIS => 'Sistema de tickets Mantis',
            self::CTIT => 'Actividades de CTIT',
            self::CORREO => 'Gestión de correos electrónicos',
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
