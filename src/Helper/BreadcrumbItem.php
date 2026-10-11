<?php

namespace App\Helper;


final class BreadcrumbItem
{

    public function __construct(
        private string $class,
        private mixed $attributes,
        private string $attributSeparator = ',',
        private string $itemSeparator = '-'
    )
    {
    }

    public function getClass() : string
    {
        return $this->class;
    }

    public function getItemSeparator()
    {
        return $this->itemSeparator;
    }

    public function getAttributes() : array
    {
        $attributes = $this->attributes;
        if (is_string($attributes) === true) {
            $attributes = explode($this->attributSeparator, $attributes);
        }
        return $attributes;
    }
}
