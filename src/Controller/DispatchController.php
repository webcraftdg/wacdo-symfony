<?php

namespace App\Controller;

use App\Attribute\PageTitle;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DispatchController extends AbstractController
{
    #[Route('', name: 'app_disptach')]
    #[IsGranted('IS_AUTHENTICATED')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_assignment_home');;
    }
}
