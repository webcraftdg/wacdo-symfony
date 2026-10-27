<?php
/**
 * UserController.php
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
use App\Entity\User;
use App\Form\AssignmentSearchType;
use App\Form\GenericSearchType;
use App\Form\UserCollaboratorType;
use App\Helper\EntityProvider;
use App\Repository\AssignmentRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('utilisateurs', name: 'app_user_')]
#[IsGranted('ROLE_RESTAURANT_OWNER')]
final class UserController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private AssignmentRepository $assignmentRepository,
        private UserPasswordHasherInterface $userPasswordHasher
    )
    {}

    /**
     * index
     *
     * List user
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route('/accueil', name: 'home')]
    #[PageTitle(title:'Accueil', section:'Collaborateurs')]
    #[Breadcrumb([
        [
            'label' => 'Accueil',
            'route' => 'app_dispatch',
        ],
        [
            'label' => 'liste des affectations',
        ]
    ])]
    public function index(Request $request): Response
    {
        $noAffected = $request->query->get('noAffected', false);
        $searchForm = $this->createForm(
            type:GenericSearchType::class,
            options: [
                'placeholder' => 'Nom, prénom, email',
                'action' => $this->generateUrl('app_user_home', ['noAffected' => $noAffected])
            ]);
        $searchForm->handleRequest($request);
        $criteria = $searchForm->isSubmitted() && $searchForm->isValid() ? $searchForm->getData()  : null;

        $queryBuilder = $this->userRepository->createCollaboratorQuery(
            noAffected:$noAffected,
            criteria:$criteria
        );

         $dataProvider = new EntityProvider(
            queryBuilder: $queryBuilder,
            request: $request,
            pageSize: 10);
        return $this->render('user/index.html.twig', [
            'dataProvider' => $dataProvider,
            'noAffected' => boolval($noAffected),
            'searchForm' => $searchForm
        ]);
    }

    /**
     * create
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route('/creer', name: 'create')]
    #[PageTitle(title:'Collaborateurs', section:'créer')]
    #[Breadcrumb([
        [
            'label' => 'Accueil',
            'route' => 'app_dispatch',
        ],
        [
            'label' => 'liste des collaborateurs',
            'route' => 'app_user_home'
        ],
        [
            'label' => 'créer un collaborateur',
        ]
    ])]
    public function create(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserCollaboratorType::class, $user, ['validation_groups' => ['user:create']]);
        $response = $this->manageUser($form, $user, $request, 'app_user_home');
        if ($response === null) {
            $response = $this->render('user/create.html.twig', [
                'form' => $form,
                'user' => $user,
                'action' => $this->generateUrl('app_user_create')
            ]);
        }
        return $response;
    }

    /**
     * async-create
     *
     * Create user with ajax
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route('/async-creer', name: 'async_create')]
    public function asyncCreate(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserCollaboratorType::class, $user, ['validation_groups' => ['user:create']]);
        $response = $this->manageUser($form, $user, $request, 'app_user_home', true);
        if ($response === null) {
            $response = $this->render('common/_form-create-collaborator.html.twig', [
                'form' => $form,
                'user' => $user,
                'action' => $this->generateUrl('app_user_async_create')
            ]);
        }
        return $response;
    }


    /**
     * Update
     *
     * @param  \App\Entity\User                           $user
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route('/{id}/mise_a_jour', name: 'update')]
    #[PageTitle(title:'Collaborateurs', section:'Mettre à jour')]
    #[Breadcrumb(
        routes: [
            [
                'label' => 'Accueil',
                'route' => 'app_dispatch',
            ],
            [
                'label' => 'liste des collaborateurs',
                'route' => 'app_user_home'
            ],
        ],
        finalItem:[User::class, ['lastname', 'firstname']]
    )]
    public function update(User $user, Request $request): Response
    {
        $form = $this->createForm(UserCollaboratorType::class, $user, ['validation_groups' => ['user:update']]);
        $response = $this->manageUser($form, $user, $request, 'app_user_home');

        if ($response === null) {
            $response = $this->render('user/update.html.twig', [
                'form' => $form,
                'user' => $user,
                'action' => $this->generateUrl('app_user_update', ['id' => $user->getId()])
            ]);
        }
        return $response;
    }

    /**
     * Detail
     *
     * @param User $user
     * @param Request $request
     *
     * @return Response
     */
    #[Route('/{id}/detail', name: 'detail')]
    #[PageTitle(title:'Collaborateurs', section:'Détail')]
    #[Breadcrumb(
        routes: [
            [
                'label' => 'Accueil',
                'route' => 'app_dispatch',
            ],
            [
                'label' => 'liste des collaborateurs',
                'route' => 'app_user_home'
            ],
        ],
        finalItem:[User::class, ['lastname', 'firstname']]
    )]
    public function detail(User $user, Request $request): Response
    {
        $searchForm = $this->createForm(
            type:AssignmentSearchType::class,
            options: [
                'placeholder' => 'Nom, adresse, code postal, ville',
                'action' => $this->generateUrl('app_user_detail', ['id' => $user->getId()]),
                'user' => $user
            ]);
        $searchForm->handleRequest($request);
        $criteria = $searchForm->isSubmitted() && $searchForm->isValid() ? $searchForm->getData()  : null;
        $queryBuilder = $this->assignmentRepository->createBuilderforUserWithCriteria(
            user:$user,
            criteria:$criteria
        );
        $dataProvider = new EntityProvider(
            queryBuilder: $queryBuilder,
            request: $request,
        pageSize: 10);

        return $this->render('user/detail.html.twig', [
            'searchForm' => $searchForm,
            'user' => $user,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * Archived
     *
     * Set date archived
     *
     * @param User $user
     * @param Request $request
     *
     * @requires Response
     */
    #[Route('/{id}/archiver', name: 'archived', methods: ['PATCH'])]
    #[IsGranted(User::ROLE_ADMIN)]
    public function archived(User $user, Request $request) : Response
    {
        if (!$this->isCsrfTokenValid('archived-user-' . $user->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }
        $user->setDateArchived(new DateTime());
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $response =  new Response(status: Response::HTTP_NO_CONTENT);
        if ($request->headers->contains('Accept', 'text/vnd.turbo-stream.html') == true) {
                $response =  $this->render('user/_rowList.stream.html.twig', [
                    'item' => $user,
                ], new Response(headers: [
                    'Content-Type' => 'text/vnd.turbo-stream.html',
                ]));
        }
        return $response;
    }

    /**
     * manage user
     *
     * @param  \Symfony\Component\Form\FormInterface           $form
     * @param  \App\Entity\User                                $user
     * @param  \Symfony\Component\HttpFoundation\Request       $request
     * @param  string                                          $routeName
     * @param  bool                                            $async
     * @param  array                                           $routeParameters
     *
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    protected function manageUser(
        FormInterface $form,
        User $user,
        Request $request,
        string $routeName,
        bool $async = false,
        array $routeParameters = []) : ?Response
    {
        $form->handleRequest($request);
        $response = null;
        if ($form->isSubmitted() === true && $form->isValid() === true) {
             $plainPassword = $form->get('plainPassword')->getData();
            if (empty($plainPassword) === false) {
                // encode the plain password
                $user->setPassword($this->userPasswordHasher->hashPassword($user, $plainPassword));
            }
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            if ($async === true) {
                $response = $this->json($user, JsonResponse::HTTP_CREATED, context:['groups' => ['user:detail']]);
            } else {
                $response = $this->redirectToRoute($routeName, $routeParameters);
            }
        }
        return $response;
    }
}
