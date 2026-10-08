<?php

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Attribute\PageTitle;
use App\Entity\User;
use App\Form\AssignmentSearchType;
use App\Form\GenericSearchType;
use App\Form\UserCollaboratorType;
use App\Helper\EntityProvider;
use App\Repository\AssigmentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user', name: 'app_user_')]
#[IsGranted('ROLE_RESTAURANT_OWNER')]
final class UserController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private AssigmentRepository $assigmentRepository,
        private UserPasswordHasherInterface $userPasswordHasher
    )
    {}

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
            ]);
        }
        return $response;
    }


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
            ]);
        }
        return $response;
    }

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
        $queryBuilder = $this->assigmentRepository->createBuilderforUserWithCriteria(
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

    #[Route('/{id}/supprimer', name: 'delete', methods: ['DELETE'])]
    #[IsGranted(User::ROLE_ADMIN)]
    public function delete(User $user) : Response
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        return $this->json(null, Response::HTTP_NO_CONTENT);;
    }

    /**
     * Manage entity
     *
     * @param  \Symfony\Component\Form\FormInterface           $form
     * @param  \App\Entity\User                                $user
     * @param  \Symfony\Component\BrowserKit\Request           $request
     * @param  string                                          $routeName
     * @param  array                                           $routeParameters
     *
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    protected function manageUser(
        FormInterface $form,
        User $user,
        Request $request,
        string $routeName,
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
            $response = $this->redirectToRoute($routeName, $routeParameters);
        }
        return $response;
    }
}
