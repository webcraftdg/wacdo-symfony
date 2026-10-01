<?php

namespace App\Enum;

enum AssisnmentStatus : string
{
    case FINI = 'fini';
    case EN_ATTENTE = 'enAttente';
    case EN_COURS = 'enCours';

    public function label() : string
    {
        return match ($this) {
            AssisnmentStatus::EN_ATTENTE => 'En attente',
            AssisnmentStatus::EN_COURS => 'En cours',
            AssisnmentStatus::FINI => 'Terminé',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'amber',
            self::EN_COURS => 'blue',
            self::FINI => 'red',
        };
    }

    public function getBorderClass(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'border-4 border-amber-500',
            self::EN_COURS => 'border-4 border-blue-500',
            self::FINI => 'border-4 border-red-500',
        };
    }
}
