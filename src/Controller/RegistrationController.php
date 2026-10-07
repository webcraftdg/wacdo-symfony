<?php

namespace App\Controller;

use App\Attribute\Breadcrumb;
use App\Attribute\PageTitle;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RegistrationController extends AbstractController
{

    #[Route('/mon-compte', name: 'app_account')]
    #[PageTitle(title:'Mettre à jour', section:'Utilisateurs')]
    #[Breadcrumb([
        [
            'label' => 'Accueil',
            'route' => 'app_home',
        ],
        [
            'label' => 'mon compte',
        ],
    ])]
    #[IsGranted('IS_AUTHENTICATED')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(RegistrationFormType::class, $user, ['validation_groups' => ['user:update']]);
        $form->handleRequest($request);
        $user->setRole((($user->getRoles()[0]) ?? 'ROLE_COLLAB'));

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            if (empty($plainPassword) === false) {
                // encode the plain password
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            }
            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_disptach');
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form,
        ]);
    }
}
