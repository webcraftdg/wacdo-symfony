<?php

namespace App\Attribute;

use App\Helper\BreadcrumbRoute;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Breadcrumb
{
   private array $routes = [];

    public function __construct(array $routes = [])
    {
        foreach ($routes as $route) {
            $this->routes[] = new BreadcrumbRoute(
                $route['label'],
                $route['route'] ?? null,
                $route['params'] ?? [],
            );
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
}
