<?php
/**
 * RestaurantController.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <david.ghyse@webcraftdg.fr>
 * @version XXX
 * @package App\Controller
 */

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
use App\Repository\AssignmentRepository;
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
        private AssignmentRepository $assignmentRepository,
        private EntityManagerInterface $entityManagerInterface
    )
    {}


    /**
     * index
     *
     * List restaurant
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route('/accueil', name: 'home')]
    #[PageTitle(title:'Liste', section:'Restaurant')]
    #[Breadcrumb([
        [
            'label' => 'Accueil',
            'route' => 'app_dispatch',
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

    /**
     * update restaurant
     *
     * @param Restaurant $restaurant
     * @param Request  $request
     *
     * @requires Response
     */
    #[Route('/{id}/mettre-a-jour', name: 'update')]
    #[PageTitle(title:'Mettre à jour', section:'Restaurant')]
    #[Breadcrumb(
        routes: [
            [
                'label' => 'Accueil',
                'route' => 'app_dispatch',
            ],
            [
                'label' => 'liste des restaurant',
                'route' => 'app_restaurant_home'
            ],
        ],
        finalItem:[Restaurant::class, 'name']
    )]
    public function update(Restaurant $restaurant, Request $request): Response
    {
        $form = $this->createForm(RestaurantType::class, $restaurant);
        $form->handleRequest($request);
        $response = null;
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $this->entityManagerInterface->persist($restaurant);
            $this->entityManagerInterface->flush();
            $response = $this->redirectToRoute('app_restaurant_home');
        }
        if ($response === null) {
            $response = $this->render('restaurant/update.html.twig', [
                'form' => $form
            ]);
        }
        return $response;
    }

    /**
     * detail restaurant
     *
     * @param Restaurant $restaurant
     * @param Request  $request
     *
     * @requires Response
     */
    #[Route('/{id}/detail', name: 'detail')]
    #[PageTitle(title:'detail', section:'Restaurant')]
    #[Breadcrumb(
        routes:[
            [
                'label' => 'Accueil',
                'route' => 'app_dispatch',
            ],
            [
                'label' => 'liste des restaurant',
                'route' => 'app_restaurant_home'
            ]
        ],
        finalItem:[Restaurant::class, 'name']
    )]
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
        $queryBuilder = $this->assignmentRepository->createBuilderforUserWithCriteria(
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

    /**
     * archive restaurant
     *
     * Set date archirverd
     *
     * @param Restaurant $restaurant
     * @param Request  $request
     *
     * @requires Response
     */
    #[Route('/{id}/archiver', name: 'archived', methods: ['PATCH'])]
    #[IsGranted(User::ROLE_ADMIN)]
    public function archived(Restaurant $restaurant, Request $request) : Response
    {
        $restaurant->setDateArchived(new DateTime());
        $this->entityManagerInterface->persist($restaurant);
        $this->entityManagerInterface->flush();
        $response =  new Response(status: Response::HTTP_NO_CONTENT);
        if ($request->headers->contains('Accept', 'text/vnd.turbo-stream.html') === true) {
            $response =  $this->render('restaurant/_rowList.stream.html.twig', [
                'item' => $restaurant,
            ], new Response(headers: [
                'Content-Type' => 'text/vnd.turbo-stream.html',
            ]));
        }
        return $response;
    }
}
