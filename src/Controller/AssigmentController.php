<?php

namespace App\Controller;

use App\Attribute\PageTitle;
use App\Entity\Assigment;
use App\Entity\User;
use App\Form\AssignmentType;
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
    public function index(): Response
    {
        $assignments = $this->assigmentRepository->findForUser($this->getUser());
        return $this->render('assigment/index.html.twig', [
            'assignments' => $assignments,
        ]);
    }


    #[Route('/creer', name: 'create')]
    #[PageTitle(title:'Affectations', section:'créer')]
    #[IsGranted(User::ROLE_RETAURANT_OWNER)]
    public function create(Request $request): Response
    {
        $assigment = new Assigment();
        $form = $this->createForm(AssignmentType::class, $assigment, ['validation_groups' => ['assignement:create']]);
        $response = $this->manageAssignement($form, $assigment, $request, 'app_assignment_home');
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
