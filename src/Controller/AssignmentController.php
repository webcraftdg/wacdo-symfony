<?php
/**
 * AssignmentController.php
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
use App\Entity\Assignment;
use App\Entity\User;
use App\Form\AssignmentSearchType;
use App\Form\AssignmentType;
use App\Helper\EntityProvider;
use App\Repository\AssignmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/affectations', name: 'app_assignment_')]
#[IsGranted('IS_AUTHENTICATED')]
final class AssignmentController extends AbstractController
{
    public function __construct(
        private AssignmentRepository $assignmentRepository,
        private EntityManagerInterface $entityManagerInterface
    )
    {}

    #[Route('/accueil', name: 'home')]
    #[IsGranted(User::ROLE_COLLAB)]
    #[PageTitle(title:'Affectations', section:'Liste')]
    #[Breadcrumb([
        [
            'label' => 'Accueil',
            'route' => 'app_dispatch',
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
        $queryBuilder = $this->assignmentRepository->createBuilderforUserWithCriteria(
            user:$this->getUser(),
            criteria:$criteria
        );
        $dataProvider = new EntityProvider(
            queryBuilder: $queryBuilder,
            request: $request,
            pageSize: 10
        );
        return $this->render('assignment/index.html.twig', [
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
            'route' => 'app_dispatch',
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
        $assignment = new Assignment();
        $form = $this->createForm(
            AssignmentType::class,
            $assignment,
            [
                'validation_groups' => ['assignement:create'],
                'user' => $user
            ]
        );
        $routeRedirectionName = 'app_assignment_home';
        $parameters = [];
        $addModelNewUser = true;
        if($user !== null) {
            $routeRedirectionName = 'app_user_detail';
            $parameters['id'] = $user->getId();
            $addModelNewUser = false;
        }
        $response = $this->manageAssignement(
            $form,
            $assignment,
            $request,
            $routeRedirectionName,
            $parameters
        );
        if ($response === null) {
            $response = $this->render('assignment/create.html.twig', [
                'form' => $form,
                'assignment' => $assignment,
                'addModelNewUser' => $addModelNewUser
            ]);
        }
        return $response;
    }


    #[Route('/{id}/mise_a_jour', name: 'update')]
    #[PageTitle(title:'Affectations', section:'Mettre à jour')]
    #[Breadcrumb(
        routes:[
            [
                'label' => 'Accueil',
                'route' => 'app_dispatch',
            ],
            [
                'label' => 'liste des affectations',
                'route' => 'app_assignment_home'
            ],
        ],
        finalItem: [Assignment::class, 'user']
    )]
    #[IsGranted(User::ROLE_RETAURANT_OWNER)]
    public function update(Assignment $assignment, Request $request): Response
    {
        $form = $this->createForm(
            AssignmentType::class,
            $assignment,
            ['validation_groups' => ['assignement:update']]);
        $response = $this->manageAssignement($form, $assignment, $request, 'app_assignment_home');
        if ($response === null) {
            $response = $this->render('assignment/update.html.twig', [
                'form' => $form,
                'assignment' => $assignment,
            ]);
        }
        return $response;
    }

    #[Route('/{id}/supprimer', name: 'delete', methods: ['DELETE'])]
    #[IsGranted(User::ROLE_RETAURANT_OWNER)]
    public function delete(Assignment $assignment) : Response
    {
        /**@var USer $user */
        $user = $this->getUser();
        if ($this->isGranted(User::ROLE_ADMIN) || in_array($assignment->getRestaurant(), $user->getRestaurants()->toArray()) === true) {
            $this->entityManagerInterface->remove($assignment);
            $this->entityManagerInterface->flush();
            $response = $this->json(null, Response::HTTP_NO_CONTENT);
        } else {
            $response = $this->json(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $response;
    }

    protected function manageAssignement(
        FormInterface $form,
        Assignment $assignment,
        Request $request,
        string $routeName,
        array $routeParameters = []) : ?Response
    {
        $form->handleRequest($request);
        $response = null;
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $this->entityManagerInterface->persist($assignment);
            $this->entityManagerInterface->flush();
            $response = $this->redirectToRoute($routeName, $routeParameters);
        }
        return $response;
    }
}
