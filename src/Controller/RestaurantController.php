<?php

namespace App\Controller;

use App\Attribute\PageTitle;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Form\RestaurantType;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

 #[Route('/restaurant', name: 'app_restaurant_')]
 #[IsGranted('IS_AUTHENTICATED')]
final class RestaurantController extends AbstractController
{

    public function __construct(
        private RestaurantRepository $restaurantRepository,
        private EntityManagerInterface $entityManagerInterface
    )
    {}


    #[Route('/accueil', name: 'home')]
    #[PageTitle(title:'Liste', section:'Restaurant')]
    #[IsGranted('ROLE_RESTAURANT_OWNER')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $restaurants = [];
        if ($this->isGranted(User::ROLE_ADMIN) === true) {
            $restaurants = $this->restaurantRepository->findAll();
        } else {
            $restaurants = $user->getRestaurants();
        }
        return $this->render('restaurant/index.html.twig', [
            'restaurants' => $restaurants,
        ]);
    }

    #[Route('/{id}/detail', name: 'detail')]
    #[PageTitle(title:'Détail', section:'Restaurant')]
    #[IsGranted('ROLE_RESTAURANT_OWNER')]
    public function detail(Restaurant $restaurant): Response
    {

        return $this->render('restaurant/detail.html.twig', [
            'restaurant' => $restaurant,
            'assignments' => $restaurant->getAssigments()
        ]);
    }

    #[Route('/{id}/mettre-a-jour', name: 'update')]
    #[PageTitle(title:'Mettre à jour', section:'Restaurant')]
    #[IsGranted('ROLE_RESTAURANT_OWNER')]
    public function update(Restaurant $restaurant, Request $request): Response
    {
        $form = $this->createForm(RestaurantType::class, $restaurant);
        $form->handleRequest($request);
        $response = null;
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $this->entityManagerInterface->persist($restaurant);
            $this->entityManagerInterface->flush();
            $response = $this->redirectToRoute('app_restaurant_detail', ['id' => $restaurant->getId()]);
        }
        if ($response === null) {
            $response = $this->render('restaurant/update.html.twig', [
                'restaurant' => $restaurant,
                'form' => $form
            ]);
        }
        return $response;
    }
}
