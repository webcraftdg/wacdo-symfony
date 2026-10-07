<?php

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Repository\AssigmentRepository;
use App\Repository\RestaurantRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

 #[IsGranted('ROLE_RESTAURANT_OWNER')]
final class HomeController extends AbstractController
{
    public function __construct(
        private RestaurantRepository $restaurantRepository,
        private UserRepository $userRepository,
        private AssigmentRepository $assignmentRepository
    )
    {
    }
    #[Route('/home', name: 'app_home')]
    #[Breadcrumb([
        [
            'label' => 'Accueil',
        ]
    ])]
    public function index(): Response
    {
        $stats = [
            'restaurants' => $this->restaurantRepository->count(),
            'collaborators' => [
                'busy' => $this->userRepository->countBusy(),
                'available' => $this->userRepository->countAvailable(),
            ],
            'assignments' => [
                'pending' => $this->assignmentRepository->countPending(),
                'current' => $this->assignmentRepository->countCurrent(),
                'finished' => $this->assignmentRepository->countFinished(),
            ],
        ];

        return $this->render('home/index.html.twig', [
            'stats' => $stats,
        ]);
    }
}
