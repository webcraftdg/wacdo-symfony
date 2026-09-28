<?php

namespace App\Controller;

use App\Attribute\PageTitle;
use App\Entity\Assigment;
use App\Form\AssignmentType;
use App\Repository\AssigmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/affectations', name: 'app_assignment_')]
final class AssigmentController extends AbstractController
{
    public function __construct(
        private AssigmentRepository $assigmentRepository,
        private EntityManagerInterface $entityManagerInterface
    )
    {}

    #[Route('/accueil', name: 'home')]
    public function index(): Response
    {
        return $this->render('assigment/index.html.twig', [
            'controller_name' => 'AssigmentController',
        ]);
    }

    #[Route('/{id}/detail', name: 'detail')]
    public function detail(): Response
    {
        return $this->render('assigment/index.html.twig', [
            'controller_name' => 'AssigmentController',
        ]);
    }

    #[Route('/{id}/mise_a_jour', name: 'update')]
    #[PageTitle(title:'Mettre à jour', section:'Affectation')]
    public function update(Assigment $assigment, Request $request): Response
    {
        $form = $this->createForm(AssignmentType::class, $assigment);
        $form->handleRequest($request);
        $response = null;
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $this->entityManagerInterface->persist($assigment);
            $this->entityManagerInterface->flush();
            $response = $this->redirectToRoute('app_restaurant_detail',
            ['id' => $assigment->getRestaurant()->getId()
            ]);
        }
        if ($response === null) {
            $response = $this->render('assigment/update.html.twig', [
                'form' => $form,
                'assigment' => $assigment,
            ]);
        }
        return $response;
    }
}
