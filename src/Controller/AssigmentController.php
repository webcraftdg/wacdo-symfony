<?php

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Attribute\PageTitle;
use App\Entity\Assigment;
use App\Entity\User;
use App\Form\AssignmentSearchType;
use App\Form\AssignmentType;
use App\Helper\EntityHydrator;
use App\Helper\EntityProvider;
use App\Repository\AssigmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/affectations', name: 'app_assignment_')]
#[IsGranted('IS_AUTHENTICATED')]
final class AssigmentController extends AbstractController
{
    public function __construct(
        private AssigmentRepository $assigmentRepository,
        private EntityManagerInterface $entityManagerInterface
    )
    {}

    #[Route('/accueil', name: 'home')]
    #[IsGranted(User::ROLE_COLLAB)]
    #[PageTitle(title:'Affectations', section:'Liste')]
    #[Breadcrumb([
        [
            'label' => 'Accueil',
            'route' => 'app_home',
        ],
        [
            'label' => 'liste des affectations',
        ],
    ])]
    public function index(Request $request): Response
    {
        $searchForm = $this->createForm(AssignmentSearchType::class,
          options: [
                'placeholder' => 'Nom, adresse, code postal, ville',
                'action' => $this->generateUrl('app_assignment_home')
            ]
        );
        $searchForm->handleRequest($request);
        $criteria = $searchForm->isSubmitted() && $searchForm->isValid() ? $searchForm->getData()  : null;
        $queryBuilder = $this->assigmentRepository->createBuilderforUserWithCriteria(
            user:$this->getUser(),
            criteria:$criteria
        );
        $dataProvider = new EntityProvider(
            queryBuilder: $queryBuilder,
            request: $request,
            pageSize: 10
        );
        return $this->render('assigment/index.html.twig', [
            'dataProvider' => $dataProvider,
            'searchForm' => $searchForm
        ]);
    }


    #[Route(
        '/creer/{user}',
        name: 'create',
        defaults: ['user' => null]
    )]
    #[PageTitle(title:'Affectations', section:'créer')]
    #[Breadcrumb([
        [
            'label' => 'Accueil',
            'route' => 'app_home',
        ],
        [
            'label' => 'liste des affectations',
            'route' => 'app_assignment_home'
        ],
        [
            'label' => 'création d\'une affectations',
        ],
    ])]
    #[IsGranted(User::ROLE_RETAURANT_OWNER)]
    public function create(Request $request, ?User $user): Response
    {
        $assigment = new Assigment();
        $form = $this->createForm(
            AssignmentType::class,
            $assigment,
            [
                'validation_groups' => ['assignement:create'],
                'user' => $user
            ]
        );
        $routeRedirectionName = 'app_assignment_home';
        $parameters = [];
        if($user !== null) {
            $routeRedirectionName = 'app_user_detail';
            $parameters['id'] = $user->getId();
        }
        $response = $this->manageAssignement(
            $form,
            $assigment,
            $request,
            $routeRedirectionName,
            $parameters
        );
        if ($response === null) {
            $response = $this->render('assigment/create.html.twig', [
                'form' => $form,
                'assigment' => $assigment,
            ]);
        }
        return $response;
    }


    #[Route('/{id}/mise_a_jour', name: 'update')]
    #[PageTitle(title:'Affectations', section:'Mettre à jour')]
    #[Breadcrumb([
        [
            'label' => 'Accueil',
            'route' => 'app_home',
        ],
        [
            'label' => 'liste des affectations',
            'route' => 'app_assignment_home'
        ],
        [
            'label' => 'affectation de : ',
        ],
    ])]
    #[IsGranted(User::ROLE_RETAURANT_OWNER)]
    public function update(Assigment $assigment, Request $request): Response
    {
        $form = $this->createForm(AssignmentType::class, $assigment, ['validation_groups' => ['assignement:update']]);
        $response = $this->manageAssignement($form, $assigment, $request, 'app_assignment_home');
        if ($response === null) {
            $response = $this->render('assigment/update.html.twig', [
                'form' => $form,
                'assigment' => $assigment,
            ]);
        }
        return $response;
    }

    #[Route('/{id}/supprimer', name: 'delete', methods: ['DELETE'])]
    #[IsGranted(User::ROLE_RETAURANT_OWNER)]
    public function delete(Assigment $assigment) : Response
    {
        /**@var USer $user */
        $user = $this->getUser();
        if ($this->isGranted(User::ROLE_ADMIN) || in_array($assigment->getRestaurant(), $user->getRestaurants()->toArray()) === true) {
            $this->entityManagerInterface->remove($assigment);
            $this->entityManagerInterface->flush();
            $response = $this->json(null, Response::HTTP_NO_CONTENT);
        } else {
            $response = $this->json(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $response;
    }

    protected function manageAssignement(
        FormInterface $form,
        Assigment $assigment,
        Request $request,
        string $routeName,
        array $routeParameters = []) : ?Response
    {
        $form->handleRequest($request);
        $response = null;
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $this->entityManagerInterface->persist($assigment);
            $this->entityManagerInterface->flush();
            $response = $this->redirectToRoute($routeName, $routeParameters);
        }
        return $response;
    }
}
