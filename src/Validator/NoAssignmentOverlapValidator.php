<?php

namespace App\Validator;

use App\Entity\Assignment;
use App\Repository\AssignmentRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NoAssignmentOverlapValidator extends ConstraintValidator
{

    public function __construct(
        private AssignmentRepository $repository)
    {
    }

	public function validate(mixed $value, Constraint $constraint)
    {
        if ($value instanceof Assignment && $value->getUser() !== null && $value->getDateStart() !== null) {

             $overlap = $this->repository->hasOverlappingAssignment(
                $value->getUser(),
                $value->getDateStart(),
                $value->getDateEnd(),
                $value
            );

            if ($overlap) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->atPath('dateStart') // rattaché l'erreur à un champs (message sur le champs dans le form)
                    ->addViolation();
            }
        }
    }
}
