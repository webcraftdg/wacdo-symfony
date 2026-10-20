<?php
/**
 * BreadcrumbRoute.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <david.ghyse@webcraftdg.fr>
 * @version XXX
 * @package App\Helper
 */
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
