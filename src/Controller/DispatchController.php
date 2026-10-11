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
    #[Route('', name: 'app_dispatch')]
    #[IsGranted('IS_AUTHENTICATED')]
    public function index(): Response
    {
        if ($this->isGranted(User::ROLE_ADMIN) === true) {
            $response = $this->redirectToRoute('app_home');
        } elseif($this->isGranted(User::ROLE_RETAURANT_OWNER) === true) {
            $response =$this->redirectToRoute('app_restaurant_home');
        }  else {
            $response =$this->redirectToRoute('app_assignment_home');
        }
        return $response;
    }
}
