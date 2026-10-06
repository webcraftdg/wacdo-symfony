<?php

namespace App\Helper;

class CriteriaField
{
    public function __construct(
        private string $attribute,
        private string $operator,
        private string $exprValue
    )
    {
    }

    public function getAttribute()
    {
        return $this->attribute;
    }

    public function getOperator()
    {
        return $this->operator;
    }

    public function getExprValue()
    {
        return $this->exprValue;
    }


    public function __toString()
    {
        return $this->attribute.' '.$this->operator.' :'.$this->exprValue;
    }
}
