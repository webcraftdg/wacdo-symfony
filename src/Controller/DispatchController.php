<?php
/**
 * DispatchController.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <david.ghyse@webcraftdg.fr>
 * @version XXX
 * @package App\Controller
 */
namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DispatchController extends AbstractController
{
    /**
     * Dispatch
     *
     * Dispatch user connected to available route
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route('', name: 'app_dispatch')]
    #[IsGranted('IS_AUTHENTICATED')]
    public function index(): Response
    {
        if ($this->isGranted(User::ROLE_ADMIN) === true) {
            $response = $this->redirectToRoute('app_home');
        } elseif($this->isGranted(User::ROLE_RESTAURANT_OWNER) === true) {
            $response =$this->redirectToRoute('app_restaurant_home');
        }  else {
            $response =$this->redirectToRoute('app_assignment_home');
        }
        return $response;
    }
}
