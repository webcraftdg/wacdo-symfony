<?php

namespace App\Attribute;

use App\Helper\BreadcrumbItem;
use App\Helper\BreadcrumbRoute;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Breadcrumb
{
   private array $routes = [];
   private ?BreadcrumbItem $finalItem = null;

    public function __construct(
        array $routes = [],
        array $finalItem = [])
    {
        foreach ($routes as $route) {
            $this->routes[] = new BreadcrumbRoute(
                $route['label'],
                $route['route'] ?? null,
                $route['params'] ?? [],
            );
        }
        if (count($finalItem) === 2) {
            $this->finalItem = new BreadcrumbItem($finalItem[0], $finalItem[1]);
        }
    }

    /**
     * Add routes
     *
     * @param  string      $label
     * @param  string|null $route
     * @param  array       $params
     *
     * @return self
     */
    public function add(string $label, ?string $route = null, array $params = []) : self
    {
        $this->routes[] = new BreadcrumbRoute($label, $route, $params);
        return $this;
    }

    /**
     * get Routes
     *
     * @return array
     */
    public function getRoutes() : array
    {
        return $this->routes;
    }

    public function getFinalItem() : ?BreadcrumbItem
    {
        return $this->finalItem;
    }
}
