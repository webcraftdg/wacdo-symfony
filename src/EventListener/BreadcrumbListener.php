<?php

namespace App\EventListener;

use App\Attribute\Breadcrumb;
use App\Helper\BreadcrumbItem;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::CONTROLLER_ARGUMENTS)]
class BreadcrumbListener
{
    public function __invoke(ControllerArgumentsEvent $event): void
    {
        $controller = $event->getController();
        $arguments = $event->getArguments();

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
        $finalItem = $breadcrumb->getFinalItem();
        if ($finalItem instanceof BreadcrumbItem) {
            $class = $finalItem->getClass();
            foreach($arguments as $argument) {
                if ($argument instanceof $class) {
                    $labels = [];
                    foreach($finalItem->getAttributes() as $attribute) {
                        $method = 'get'.ucfirst($attribute);
                        if (method_exists($argument, $method)=== true) {
                            $labels[] = $argument->$method();
                        }
                    }
                    $breadcrumb->add(implode($finalItem->getItemSeparator(), $labels));
                    break;
                }
            }
        }

        $event->getRequest()->attributes->set(
            'breadcrumb',
            $breadcrumb->getRoutes()
        );
    }
}
