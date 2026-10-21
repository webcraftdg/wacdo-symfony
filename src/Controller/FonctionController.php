<?php
/**
 * FonctionController.php
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
use App\Entity\Fonction;
use App\Entity\User;
use App\Form\FonctionType;
use App\Form\GenericSearchType;
use App\Helper\EntityHydrator;
use App\Repository\FonctionRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

 #[Route('/fonctions', name: 'app_fonction_')]
 #[IsGranted('ROLE_ADMIN')]
final class FonctionController extends AbstractController
{

    public function __construct(
        private FonctionRepository $fonctionrepository,
        private EntityManagerInterface $entityManagerInterface
    )
    {}


    /**
     * index
     *
     * List restaurant
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route('/accueil', name: 'home')]
    #[PageTitle(title:'Liste', section:'Fonction')]
    #[Breadcrumb([
        [
            'label' => 'Accueil',
            'route' => 'app_dispatch',
        ],
        [
            'label' => 'liste des fonction',
        ]
    ])]
    public function index(Request $request): Response
    {
        $searchForm = $this->createForm(
            type:GenericSearchType::class,
            options: [
                'placeholder' => 'Nom de la fonction',
                'action' => $this->generateUrl('app_fonction_home')
            ]);
        $searchForm->handleRequest($request);
        $criteria = $searchForm->isSubmitted() && $searchForm->isValid() ? $searchForm->getData()  : null;
        $items = new EntityHydrator(
            queryBuilder:$this->fonctionrepository->createBuilder(
                displayArchived:$this->isGranted(User::ROLE_ADMIN),
                criteria:$criteria
            )
        );
        return $this->render('fonction/index.html.twig', [
            'items' => $items,
            'searchForm' => $searchForm
        ]);
    }

    /**
     * create fonction
     *
     * @param Request  $request
     *
     * @requires Response
     */
    #[Route('/creer', name: 'create')]
    #[PageTitle(title:'Créer', section:'Fonction')]
    #[Breadcrumb(
        routes: [
            [
                'label' => 'Accueil',
                'route' => 'app_dispatch',
            ],
            [
                'label' => 'liste des fonctions',
                'route' => 'app_fonction_home'
            ],
             [
            'label' => 'création d\'une fonction',
        ],
        ]
    )]
    public function create(Request $request): Response
    {
        $fonction = new Fonction();
        $form = $this->createForm(FonctionType::class, $fonction);
        $form->handleRequest($request);
        $response = null;
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $this->entityManagerInterface->persist($fonction);
            $this->entityManagerInterface->flush();
            $response = $this->redirectToRoute('app_fonction_home');
        }
        if ($response === null) {
            $response = $this->render('fonction/create.html.twig', [
                'form' => $form
            ]);
        }
        return $response;
    }

    /**
     * update fonction
     *
     * @param Fonction $fonction
     * @param Request  $request
     *
     * @requires Response
     */
    #[Route('/{id}/mettre-a-jour', name: 'update')]
    #[PageTitle(title:'Mettre à jour', section:'Fonction')]
    #[Breadcrumb(
        routes: [
            [
                'label' => 'Accueil',
                'route' => 'app_dispatch',
            ],
            [
                'label' => 'liste des fonctions',
                'route' => 'app_fonction_home'
            ],
        ],
        finalItem:[Fonction::class, 'name']
    )]
    public function update(Fonction $fonction, Request $request): Response
    {
        $form = $this->createForm(FonctionType::class, $fonction);
        $form->handleRequest($request);
        $response = null;
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $this->entityManagerInterface->persist($fonction);
            $this->entityManagerInterface->flush();
            $response = $this->redirectToRoute('app_fonction_home');
        }
        if ($response === null) {
            $response = $this->render('fonction/update.html.twig', [
                'form' => $form
            ]);
        }
        return $response;
    }

    /**
     * archive restaurant
     *
     * Set date archirverd
     *
     * @param Fonction $fonction
     * @param Request  $request
     *
     * @requires Response
     */
    #[Route('/{id}/archiver', name: 'archived', methods: ['PATCH'])]
    #[IsGranted(User::ROLE_ADMIN)]
    public function archived(Fonction $fonction, Request $request) : Response
    {
        $fonction->setDateArchived(new DateTime());
        $this->entityManagerInterface->persist($fonction);
        $this->entityManagerInterface->flush();
        $response =  new Response(status: Response::HTTP_NO_CONTENT);
        if ($request->headers->contains('Accept', 'text/vnd.turbo-stream.html') === true) {
            $response =  $this->render('fonction/_rowList.stream.html.twig', [
                'item' => $fonction,
            ], new Response(headers: [
                'Content-Type' => 'text/vnd.turbo-stream.html',
            ]));
        }
        return $response;
    }
}
