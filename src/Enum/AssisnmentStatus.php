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

    public function getBadgeClass(): string
    {
        return match ($this) {
            self::EN_ATTENTE  => 'bg-amber-100 text-amber-800',
            self::EN_COURS  => 'bg-blue-100 text-blue-800',
            self::FINI => 'bg-red-200 text-red-700',
        };
    }

    public function getAdminBadgeClass(): string
    {
        return match ($this) {
            self::EN_ATTENTE  => 'badge text-bg-warning',
            self::EN_COURS  => 'badge text-bg-info',
            self::FINI => 'badge text-bg-danger',
        };
    }
}
