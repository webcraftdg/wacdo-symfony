<?php

namespace App\Enum;

enum AssisnmentStatus : string
{
    case VALIDER = 'valider';
    case REFUSER = 'refuser';
    case EN_ATTENTE = 'enAttente';
    case EN_COURS = 'enCours';

    public function label() : string
    {
        return match ($this) {
            AssisnmentStatus::EN_ATTENTE => 'En attente',
            AssisnmentStatus::EN_COURS => 'En cours',
            AssisnmentStatus::REFUSER => 'Refuser',
            AssisnmentStatus::VALIDER => 'Valider',
        };
    }
}
