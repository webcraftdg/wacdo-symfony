<?php

namespace App\Validator;

use App\Entity\Assigment;
use App\Repository\AssigmentRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NoAssignmentOverlapValidator extends ConstraintValidator
{

    public function __construct(
        private AssigmentRepository $repository)
    {
    }

	public function validate(mixed $value, Constraint $constraint)
    {
        if ($value instanceof Assigment && $value->getUser() !== null && $value->getDateStart() !== null) {

             $overlap = $this->repository->hasOverlappingAssignment(
                $value->getUser(),
                $value->getDateStart(),
                $value->getDateEnd()
            );

            if ($overlap) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->setParameter('{{ collab }}', $value->getUser())
                    ->atPath('dateStart') // rattaché l'erreur à un champs (message sur le champs dans le form)
                    ->addViolation();
            }
        }
    }
}
