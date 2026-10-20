<?php
/**
 * MenuExtension.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <david.ghyse@webcraftdg.fr>
 * @version XXX
 * @package App\Twig
 */
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
