<?php

namespace App\EventListener;

use App\Attribute\Breadcrumb;
use ReflectionMethod;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::CONTROLLER)]
class BreadcrumbListener
{
    public function __invoke(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        [$controllerObject, $method] = $controller;

        $reflection = new ReflectionMethod(
            $controllerObject,
            $method
        );

        $attributes = $reflection->getAttributes(Breadcrumb::class);

        if ($attributes === []) {
            return;
        }

        /** @var Breadcrumb $breadcrumb */
        $breadcrumb = $attributes[0]->newInstance();

        $event->getRequest()->attributes->set(
            'breadcrumb',
            $breadcrumb->getRoutes()
        );
    }
}
