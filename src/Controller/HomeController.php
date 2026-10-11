<?php

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Helper\ChartDataBuilder;
use App\Repository\AssigmentRepository;
use App\Repository\RestaurantRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

 #[IsGranted('ROLE_ADMIN')]
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
                'total' => $this->userRepository->countTotat(),
                'busy' => $this->userRepository->countBusy(),
                'available' => $this->userRepository->countAvailable(),
            ],
            'assignments' => [
                'pending' => $this->assignmentRepository->countPending(),
                'current' => $this->assignmentRepository->countCurrent(),
                'finished' => $this->assignmentRepository->countFinished(),
                'repartitionByrestaurant' => $this->assignmentRepository->countCurrentByRestaurant(),
            ],
        ];
        $charDataCollab = (new ChartDataBuilder())
        ->addLabel('Collaborateurs actifs')
        ->addLabel('Collaborateurs disponibles')
        ->addDataset('Total', [$stats['collaborators']['busy'], $stats['collaborators']['available']]);

        $charDataRestaurant = new ChartDataBuilder();
        $nbRepart = [];
        foreach($stats['assignments']['repartitionByrestaurant'] as $statResto) {
            $charDataRestaurant->addLabel(($statResto['name'] ?? ''));
            $nbRepart[] = ($statResto['nb']) ?? 0;
        }
        $charDataRestaurant->addDataset('Total', $nbRepart);



        return $this->render('home/index.html.twig', [
            'stats' => $stats,
            'charDataCollab' => $charDataCollab->toArray(),
            'charDataRestaurant' => $charDataRestaurant->toArray(),
        ]);
    }
}
