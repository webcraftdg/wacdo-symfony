<?php

namespace App\Tests\Functionnal;

use App\Entity\Assignment;
use App\Entity\Fonction;
use App\Entity\Restaurant;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AssignmentControllerTest extends WebTestCase
{
    private User $admin;
    private User $collab;
    private User $collab2;
    private User $owner;
    private Restaurant $restaurant;
    private Fonction $fonction;
    private EntityManagerInterface $entityManager;
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $userPasswordHasher = static::getContainer()
        ->get(UserPasswordHasherInterface::class);

        $this->entityManager = static::getContainer()
        ->get(EntityManagerInterface::class);

        $this->admin = (new User())
        ->setDateCreate(new DateTime())
        ->setDateRecruitment(new DateTime())
        ->setEmail('test@webcrafdg.fr')
        ->setFirstname('Admin')
        ->setLastname('Admin')
        ->setDateUpdate(new DateTime())
        ->setRoles([User::ROLE_ADMIN]);
        $this->admin->setPassword($userPasswordHasher->hashPassword($this->admin, 'motDePasseTest'));
        $this->entityManager->persist($this->admin);

        $this->owner = (new User())
        ->setDateCreate(new DateTime())
        ->setDateRecruitment(new DateTime())
        ->setEmail('owner@webcrafdg.fr')
        ->setFirstname('Owner')
        ->setLastname('Owner')
        ->setDateUpdate(new DateTime())
        ->setRoles([User::ROLE_RESTAURANT_OWNER]);
        $this->owner->setPassword($userPasswordHasher->hashPassword($this->owner, 'motDePasseTest'));

        $this->entityManager->persist($this->owner);

        $this->collab = (new User())
        ->setDateCreate(new DateTime())
        ->setDateRecruitment(new DateTime())
        ->setEmail('collab@webcrafdg.fr')
        ->setFirstname('Collab')
        ->setLastname('Collab')
        ->setDateUpdate(new DateTime())
        ->setRoles([User::ROLE_COLLAB]);
        $this->collab->setPassword($userPasswordHasher->hashPassword($this->collab, 'motDePasseTest'));
        $this->entityManager->persist($this->collab);


        $this->collab2= (new User())
        ->setDateCreate(new DateTime())
        ->setDateRecruitment(new DateTime())
        ->setEmail('collab2@webcrafdg.fr')
        ->setFirstname('collab2')
        ->setLastname('collab2')
        ->setDateUpdate(new DateTime())
        ->setRoles([User::ROLE_COLLAB]);
        $this->collab2->setPassword($userPasswordHasher->hashPassword($this->collab2, 'motDePasseTest'));
        $this->entityManager->persist($this->collab2);


        $this->restaurant = (new Restaurant)
        ->setName('Mon restaurant')
        ->setAddress('25 avenue de provence')
        ->setZipCode(78140)
        ->setCity('Velizy')
        ->setOwner($this->owner)
        ->setDateCreate(new DateTime())
        ->setDateUpdate(new DateTime());

        $this->entityManager->persist($this->restaurant);

        $this->fonction = (new Fonction)->setName('Cuisinier');
        $this->entityManager->persist($this->fonction);

        $this->entityManager->flush();
        parent::setUp();
    }

    public function testIndex()
    {

        $this->client->loginUser($this->admin);

        $crawler = $this->client->request('GET', '/home');

        $code = $this->client->getResponse()->getStatusCode();
        if ($code === 500) {
            fwrite(STDERR, $this->client->getResponse()->getContent());
        }
        $this->assertEquals(200, $code);

        $link = $crawler->selectLink('Affectations')->link();

        $this->client->click($link);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Les affectations');
    }

    public function testCreateOK()
    {
        $this->client->loginUser($this->admin);

        $crawler = $this->client->request('GET', '/home');

        $link = $crawler->selectLink('Affectations')->link();

        $crawler = $this->client->click($link);
        $link = $crawler->selectLink('Créer')->link();

        $crawler = $this->client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer une affectation');
        $dateStart = new DateTime();
        $dateStart->modify('tomorrow');
        $dateEnd = new DateTime();
        $dateEnd->modify('+1 months');
        $form = $crawler->selectButton('Enregistrer')->form([
            'assignment[dateStart]' => $dateStart->format('Y-m-d'),
            'assignment[dateEnd]' => $dateEnd->format('Y-m-d'),
            'assignment[user]' => $this->collab->getId(),
            'assignment[restaurant]' => $this->restaurant->getId(),
            'assignment[fonction]' => $this->fonction->getId(),
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/affectations/accueil');

        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();


        $assignment = $this->entityManager
            ->getRepository(Assignment::class)
            ->findOneBy(['user' => $this->collab]);

        self::assertNotNull($assignment);
        $selector = 'tr#assignment_'.$assignment->getId().' > td';
        $this->assertSelectorTextContains($selector, $this->restaurant->getName());
    }

    public function testCreateDateKO()
    {
        $this->client->loginUser($this->admin);

        $crawler = $this->client->request('GET', '/home');

        $link = $crawler->selectLink('Affectations')->link();

        $crawler = $this->client->click($link);
        $link = $crawler->selectLink('Créer')->link();

        $crawler = $this->client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer une affectation');
        $dateStart = new DateTime();
        $dateStart->modify('tomorrow');
        $dateEnd = new DateTime();
        $dateEnd->modify('yesterday');
        $form = $crawler->selectButton('Enregistrer')->form([
            'assignment[dateStart]' => $dateStart->format('Y-m-d'),
            'assignment[dateEnd]' => $dateEnd->format('Y-m-d'),
            'assignment[user]' => $this->collab->getId(),
            'assignment[restaurant]' => $this->restaurant->getId(),
            'assignment[fonction]' => $this->fonction->getId(),
        ]);
        $this->client->submit($form);
        $this->client->submit($form);
        $this->assertResponseIsUnprocessable();
        $this->assertSelectorTextContains(
            'ul.text-red-600 > li',
            'La date de fin doit être postérieure ou égale à la date de début.'
        );
    }
    public function testCreatePeriodKO()
    {
        $this->client->loginUser($this->admin);

        $crawler = $this->client->request('GET', '/home');

        $link = $crawler->selectLink('Affectations')->link();

        $crawler = $this->client->click($link);
        $link = $crawler->selectLink('Créer')->link();
        //CREATE OK
        $crawler = $this->client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer une affectation');
        $dateStart = new DateTime();
        $dateStart->modify('tomorrow');
        $dateEnd = new DateTime();
        $dateEnd->modify('+1 months');
        $form = $crawler->selectButton('Enregistrer')->form([
            'assignment[dateStart]' => $dateStart->format('Y-m-d'),
            'assignment[dateEnd]' => $dateEnd->format('Y-m-d'),
            'assignment[user]' => $this->collab->getId(),
            'assignment[restaurant]' => $this->restaurant->getId(),
            'assignment[fonction]' => $this->fonction->getId(),
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/affectations/accueil');

        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();


        $assignment = $this->entityManager
            ->getRepository(Assignment::class)
            ->findOneBy(['user' => $this->collab]);

        self::assertNotNull($assignment);
        $selector = 'tr#assignment_'.$assignment->getId().' > td';
        $this->assertSelectorTextContains($selector, $this->restaurant->getName());
        //CREATE KO
        $link = $crawler->selectLink('Affectations')->link();

        $crawler = $this->client->click($link);
        $link = $crawler->selectLink('Créer')->link();

        $crawler = $this->client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer une affectation');
        $dateStart = new DateTime();
        $dateStart->modify('today');
        $dateEnd = new DateTime();
        $dateEnd->modify('+15 days');
        $form = $crawler->selectButton('Enregistrer')->form([
            'assignment[dateStart]' => $dateStart->format('Y-m-d'),
            'assignment[dateEnd]' => $dateEnd->format('Y-m-d'),
            'assignment[user]' => $this->collab->getId(),
            'assignment[restaurant]' => $this->restaurant->getId(),
            'assignment[fonction]' => $this->fonction->getId(),
        ]);
        $crawler = $this->client->submit($form);
        $this->assertResponseIsUnprocessable();
        $this->assertSelectorTextContains(
            'ul.text-red-600 > li',
            'Ce Collaborateur est déjà plannifier sur toute ou en partie de la période sélectionnée.'
        );
        //CREATE OK
          $dateStart = new DateTime();
        $dateStart->modify('today');
        $dateEnd = new DateTime();
        $dateEnd->modify('+15 days');
        $form = $crawler->selectButton('Enregistrer')->form([
            'assignment[dateStart]' => $dateStart->format('Y-m-d'),
            'assignment[dateEnd]' => $dateEnd->format('Y-m-d'),
            'assignment[user]' => $this->collab2->getId(),
            'assignment[restaurant]' => $this->restaurant->getId(),
            'assignment[fonction]' => $this->fonction->getId(),
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/affectations/accueil');

        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();


        $assignment = $this->entityManager
            ->getRepository(Assignment::class)
            ->findOneBy(['user' => $this->collab2]);

        self::assertNotNull($assignment);
        $selector = 'tr#assignment_'.$assignment->getId().' > td';
        $this->assertSelectorTextContains($selector, $this->restaurant->getName());
    }
}
