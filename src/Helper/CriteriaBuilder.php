<?php

namespace App\Helper;

class CriteriaBuilder
{
    private array $criteriaFields = [];

    public function getExpression()
    {
        $expr = [];
        foreach($this->criteriaFields as $criteriaItem) {
            if($criteriaItem instanceof CriteriaField) {
                $expr[] = $criteriaItem->__toString();
            }
        }
        return $expr;
    }

    public function add(CriteriaField $criteriaField) {
        if (is_array($this->criteriaFields) === false) {
            $this->criteriaFields = [];
        }
        $this->criteriaFields[$criteriaField->getAttribute()] = $criteriaField;
    }

    public function remove(string $attribute) {
        unset($this->criteriaFields[$attribute]);
    }
}
