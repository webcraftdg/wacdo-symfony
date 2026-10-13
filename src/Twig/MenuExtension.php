<?php

namespace App\Twig;

use App\Menu\MenuBuilder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class MenuExtension extends AbstractExtension
{
    public function __construct(
        private readonly MenuBuilder $menuBuilder
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'main_menu',
                [$this->menuBuilder, 'build']
            ),
        ];
    }
}
