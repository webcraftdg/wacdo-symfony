<?php

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Attribute\PageTitle;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Form\AssignmentSearchType;
use App\Form\GenericSearchType;
use App\Form\RestaurantType;
use App\Helper\EntityHydrator;
use App\Helper\EntityProvider;
use App\Repository\AssigmentRepository;
use App\Repository\RestaurantRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

 #[Route('/restaurant', name: 'app_restaurant_')]
 #[IsGranted('ROLE_RESTAURANT_OWNER')]
final class RestaurantController extends AbstractController
{

    public function __construct(
        private RestaurantRepository $restaurantRepository,
        private AssigmentRepository $assigmentRepository,
        private EntityManagerInterface $entityManagerInterface
    )
    {}


    #[Route('/accueil', name: 'home')]
    #[PageTitle(title:'Liste', section:'Restaurant')]
    #[Breadcrumb([
        [
            'label' => 'Accueil',
            'route' => 'app_home',
        ],
        [
            'label' => 'liste des restaurant',
        ]
    ])]
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $searchForm = $this->createForm(
            type:GenericSearchType::class,
            options: [
                'placeholder' => 'Nom, adresse, code postal, ville',
                'action' => $this->generateUrl('app_restaurant_home')
            ]);
        $searchForm->handleRequest($request);
        $criteria = $searchForm->isSubmitted() && $searchForm->isValid() ? $searchForm->getData()  : null;
        $items = new EntityHydrator(
            queryBuilder:$this->restaurantRepository->createBuilderForUser(
                user: $user,
                criteria:$criteria
            )
        );
        return $this->render('restaurant/index.html.twig', [
            'items' => $items,
            'searchForm' => $searchForm
        ]);
    }

    #[Route('/{id}/mettre-a-jour', name: 'update')]
    #[PageTitle(title:'Mettre à jour', section:'Restaurant')]
    #[Breadcrumb([
        [
            'label' => 'Accueil',
            'route' => 'app_home',
        ],
        [
            'label' => 'liste des restaurant',
            'route' => 'app_restaurant_home'
        ],
        [
            'label' => 'mettre à jour de : ',
        ],
    ])]
    public function update(Restaurant $restaurant, Request $request): Response
    {
        $form = $this->createForm(RestaurantType::class, $restaurant);
        $form->handleRequest($request);
        $response = null;
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $this->entityManagerInterface->persist($restaurant);
            $this->entityManagerInterface->flush();
            $response = $this->redirectToRoute('app_restaurant_home', ['id' => $restaurant->getId()]);
        }
        if ($response === null) {
            $response = $this->render('restaurant/update.html.twig', [
                'form' => $form
            ]);
        }
        return $response;
    }

    #[Route('/{id}/detail', name: 'detail')]
    #[PageTitle(title:'detail', section:'Restaurant')]
    #[Breadcrumb([
        [
            'label' => 'Accueil',
            'route' => 'app_home',
        ],
       [
            'label' => 'liste des restaurant',
            'route' => 'app_restaurant_home'
        ],
        [
            'label' => 'détail de : ',
        ],
    ])]
    public function detail(Restaurant $restaurant, Request $request): Response
    {
        $searchForm = $this->createForm(
            type:AssignmentSearchType::class,
            options: [
                'placeholder' => 'Nom, adresse, code postal, ville',
                'restaurant' => $restaurant,
                'action' => $this->generateUrl('app_restaurant_detail', ['id' => $restaurant->getId()])
            ]);
        $searchForm->handleRequest($request);
        $criteria = $searchForm->isSubmitted() && $searchForm->isValid() ? $searchForm->getData()  : null;
        $queryBuilder = $this->assigmentRepository->createBuilderforUserWithCriteria(
            user:$this->getUser(),
            restaurant:$restaurant,
            criteria:$criteria
        );
        $dataProvider = new EntityProvider(
            queryBuilder: $queryBuilder,
            request: $request,
            pageSize: 10);

        return $this->render('restaurant/detail.html.twig', [
            'restaurant' => $restaurant,
            'dataProvider' => $dataProvider,
            'searchForm' => $searchForm
        ]);
    }

    #[Route('/{id}/supprimer', name: 'delete', methods: ['DELETE'])]
    #[IsGranted(User::ROLE_ADMIN)]
    public function delete(Restaurant $restaurant) : Response
    {
        $restaurant->setDateArchived(new DateTime());
        $this->entityManagerInterface->persist($restaurant);
        $this->entityManagerInterface->flush();
        return $this->json(null, Response::HTTP_NO_CONTENT);;
    }
}
