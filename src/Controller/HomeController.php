<?php
/**
 * HomeController.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <david.ghyse@webcraftdg.fr>
 * @version XXX
 * @package App\Controller
 */

namespace App\Controller;

use App\Helper\ChartDataBuilder;
use App\Repository\AssignmentRepository;
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
        private AssignmentRepository $assignmentRepository
    )
    {
    }

    /**
     * index
     */
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        $stats = [
            'restaurants' => $this->restaurantRepository->count(),
            'collaborators' => [
                'total' => $this->userRepository->countTotal(),
                'busy' => $this->userRepository->countBusy(),
                'available' => $this->userRepository->countAvailable(),
            ],
            'assignments' => [
                'total' => $this->assignmentRepository->count(),
                'pending' => $this->assignmentRepository->countPending(),
                'current' => $this->assignmentRepository->countCurrent(),
                'finished' => $this->assignmentRepository->countFinished(),
                'repartitionByrestaurant' => $this->restaurantRepository->countAssignmentCurrentByRestaurant(),
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
