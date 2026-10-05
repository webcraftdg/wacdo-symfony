<?php

namespace App\Controller;

use App\Attribute\PageTitle;
use App\Entity\User;
use App\Form\GenericSearchType;
use App\Form\RegistrationFormType;
use App\Form\UserCollaboratorType;
use App\Helper\EntityHydrator;
use App\Repository\AssigmentRepository;
use App\Repository\UserRepository;
use DateTime;
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

        $queryCollaborator = $this->userRepository->createCollaboratorQuery(
            noAffected:$noAffected,
            criteria:$criteria
        );
        if (boolval($noAffected) === true) {
            $collaborators = $queryCollaborator->getQuery()->getResult();
        } else {
            $collaborators = new EntityHydrator($queryCollaborator);
        }
        return $this->render('user/index.html.twig', [
            'collaborators' => $collaborators,
            'noAffected' => boolval($noAffected),
            'searchForm' => $searchForm
        ]);
    }

    #[Route('/creer', name: 'create')]
    #[PageTitle(title:'Collaborateurs', section:'créer')]
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
    public function update(User $user, Request $request): Response
    {
        $form = $this->createForm(UserCollaboratorType::class, $user, ['validation_groups' => ['user:update']]);
        $response = $this->manageUser($form, $user, $request, 'app_user_home');
        $queryAssignments = $this->assigmentRepository->findForUser($user);
        $hydrator = new EntityHydrator($queryAssignments);

        if ($response === null) {
            $response = $this->render('user/update.html.twig', [
                'form' => $form,
                'user' => $user,
                'hydrator' => $hydrator
            ]);
        }
        return $response;
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
