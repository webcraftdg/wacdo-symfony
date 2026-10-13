<?php

namespace App\Menu;

use Symfony\Bundle\SecurityBundle\Security;

class MenuBuilder
{
    public function __construct(
        private readonly Security $security
    ) {}

    public function build(): array
    {
        $items = [];
        if ($this->security->isGranted('ROLE_RESTAURANT_OWNER')) {
            $items[] = [
                'label' => 'Restaurants',
                'route' => 'app_restaurant_home',
                'activePattern' => 'app_restaurant_',
            ];
            $items[] = [
                'label' => 'Collaborateurs',
                'route' => 'app_user_home',
                'activePattern' => 'app_user_',
            ];
        }
        $items[] =  [
            'label' => 'Affectations',
            'route' => 'app_assignment_home',
            'activePattern' => 'app_assignment_',
        ];

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $items[] = [
                'label' => 'Administration',
                'route' => 'admin',
                'activePattern' => 'admin',
            ];
        }
        $items[] = [
            'label' => 'Mon compte',
            'route' => 'app_account',
            'activePattern' => 'app_account',
        ];
        return $items;
    }
}
