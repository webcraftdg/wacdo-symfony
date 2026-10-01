<?php

namespace App\EventListener;

use App\Attribute\PageTitle;
use ReflectionMethod;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::CONTROLLER)]
class PageTitleListener
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

        $attributes = $reflection->getAttributes(PageTitle::class);

        if ($attributes === []) {
            return;
        }

        /** @var PageTitle $pageTitle */
        $pageTitle = $attributes[0]->newInstance();

        $event->getRequest()->attributes->set(
            'page_title',
            (string)$pageTitle
        );
    }
}
