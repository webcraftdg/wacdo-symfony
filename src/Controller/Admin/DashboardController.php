<?php

namespace App\Controller\Admin;

use App\Attribute\PageTitle;
use App\Repository\AssigmentRepository;
use App\Repository\FonctionRepository;
use App\Repository\RestaurantRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Override;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AdminDashboard(routePath: '/admin', routeName: 'admin', routes: [
    'index' => ['routePath' => '/liste'],
    'new' => ['routePath' => '/creer', 'routeName' => 'create'],
    'edit' => ['routePath' => '/editer/{entityId}', 'routeName' => 'edit'],
    'delete' => ['routePath' => '/supprimer/{entityId}', 'routeName' => 'delete'],
    'detail' => ['routePath' => '/detail/{entityId}', 'routeName' => 'detail'],
])]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private UserRepository $userRepository,
        private AssigmentRepository $assigmentRepository,
        private FonctionRepository $fonctionRepository,
        private RestaurantRepository $restaurantRepository
    )
    {}

    public function index(): Response
    {
        return $this->render(
            'templatesAdmin/dashboard/index.html.twig',
            [
                'statistics' => [
                    'users' => $this->userRepository->count(['isAdmin' => false]),
                    'restaurants' => $this->restaurantRepository->count(),
                    'assignments' => $this->assigmentRepository->count(),
                    'functions' => $this->fonctionRepository->count()
                ],
                'pastAssignments' => $this->assigmentRepository->findCriteriaAssignments(),
                'upcomingAssignments' => $this->assigmentRepository->findCriteriaAssignments(criteria:'a.dateEnd >= :today')
            ]);
    }


    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Wacdo');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkTo(UserCrudController::class, 'Les utilisateurs', 'fas fa-users');
        yield MenuItem::linkTo(FonctionCrudController::class, 'Les fonctions', 'fas fa-briefcase');
        yield MenuItem::linkTo(RestaurantCrudController::class, 'Les restaurants', 'fas fa-utensils');
        yield MenuItem::linkTo(AssigmentCrudController::class, 'Les affectations', 'fas fa-person-walking-luggage');

    }
}
