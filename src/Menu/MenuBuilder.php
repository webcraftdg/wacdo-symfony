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
        if ($this->security->isGranted('IS_AUTHENTICATED')) {
            $items[] =  [
                'label' => 'Affectations',
                'route' => 'app_assignment_home',
                'activePattern' => 'app_assignment_',
            ];
        }
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $items[] = [
                'label' => 'Administration',
                'route' => 'admin',
                'activePattern' => 'admin',
            ];
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $items[] = [
                'label' => 'Fonctions',
                'route' => 'app_fonction_home',
                'activePattern' => 'app_fonction_',
            ];
        }

        if ($this->security->isGranted('IS_AUTHENTICATED')) {
            $items[] = [
                'label' => 'Mon&nbsp;compte',
                'route' => 'app_account',
                'activePattern' => 'app_account',
            ];
        }

        return $items;
    }
}
