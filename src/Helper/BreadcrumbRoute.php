<?php

namespace App\Helper;


final class BreadcrumbRoute
{

    public function __construct(
        private string $label,
        private ?string $route = null,
        private array $params = []
    )
    {
    }

    public function getLabel() : string
    {
        return $this->label;
    }

    public function getRoute() : ?string
    {
        return $this->route;
    }

    public function getParams() : array
    {
        return $this->params;
    }
}
