<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class NoAssignmentOverlap extends Constraint
{
    public string $message;

    public function __construct(
        string $message,
        mixed $options = null,
        ?array $groups = null,
        mixed $payload = null
    )
    {
        $this->message = ($message) ?? $this->message;
        return parent::__construct($options, $groups, $payload);
    }

    public function validatedBy(): string
    {
        //Renvoie la classe du validator
        return static::class.'Validator';
    }

    public function getTargets(): string
    {
        //Pour appliquer la contrainte sur la classe
        return self::CLASS_CONSTRAINT;
    }
}
