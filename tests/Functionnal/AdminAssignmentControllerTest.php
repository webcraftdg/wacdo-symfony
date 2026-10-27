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

class AdminAssignmentControllerTest extends WebTestCase
{
    private User $admin;
    private User $owner;
    private User $collab;
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



        $this->restaurant = (new Restaurant)
        ->setName('Mon restaurant')
        ->setAddress('25 avenue de provence')
        ->setZipCode(78140)
        ->setCity('Velizy')
        ->setOwner($this->owner)
        ->setDateCreate(new DateTime())
        ->setDateUpdate(new DateTime());
        $this->entityManager->persist($this->restaurant);


        $this->fonction = (new Fonction)
        ->setName('Cuisinier');
        $this->entityManager->persist($this->fonction);

        $this->entityManager->flush();
        parent::setUp();
    }

    public function testIndex()
    {

        $this->client->loginUser($this->admin);

        $crawler = $this->client->request('GET', '/admin');

        $code = $this->client->getResponse()->getStatusCode();
        if ($code === 500) {
            fwrite(STDERR, $this->client->getResponse()->getContent());
        }
        $this->assertEquals(200, $code);

        $link = $crawler->selectLink('Les affectations')->link();

        $this->client->click($link);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Administration : Liste des affectations');
    }

    public function testSumitDateIntersect()
    {
        $this->client->loginUser($this->admin);

        $crawler = $this->client->request('GET', 'admin');

        $link = $crawler->selectLink('Les affectations')->link();

        $crawler = $this->client->click($link);

        $link = $crawler->selectLink('Ajouter une affectation')->link();

        $crawler = $this->client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Administration : Créer une affectation');

        $dateStart = new DateTime();
        $dateStart->modify('tomorrow');
        $dateEnd = new DateTime();
        $dateEnd->modify('+1 months');
        $form = $crawler->filter('button[value="saveAndAddAnother"]')->form([
            'Assignment[dateStart]' => $dateStart->format('Y-m-d'),
            'Assignment[dateEnd]' => $dateEnd->format('Y-m-d'),
            'Assignment[user]' => $this->collab->getId(),
            'Assignment[restaurant]' => $this->restaurant->getId(),
            'Assignment[fonction]' => $this->fonction->getId(),
        ]);
        $crawler = $this->client->submit($form);

        $this->assertResponseRedirects('/admin/assignment/creer');

        $assignment = $this->entityManager
            ->getRepository(Assignment::class)
            ->findOneBy(['user' => $this->collab]);

        self::assertNotNull($assignment);

        $crawler = $this->client->followRedirect();
        //TEST KO
        $form = $crawler->filter('button[value="saveAndReturn"]')->form([
            'Assignment[dateStart]' => $dateStart->format('Y-m-d'),
            'Assignment[dateEnd]' => $dateEnd->format('Y-m-d'),
            'Assignment[user]' => $this->collab->getId(),
            'Assignment[restaurant]' => $this->restaurant->getId(),
            'Assignment[fonction]' => $this->fonction->getId(),
        ]);
        $this->client->submit($form);
        $this->assertResponseIsUnprocessable();
        $this->assertSelectorTextContains(
            '.invalid-feedback',
            'Ce Collaborateur est déjà plannifier sur toute ou en partie de la période sélectionnée.'
        );

    }



    public function testSumitDateNotValid()
    {
        $this->client->loginUser($this->admin);

        $crawler = $this->client->request('GET', 'admin');

        $link = $crawler->selectLink('Les affectations')->link();

        $crawler = $this->client->click($link);

        $link = $crawler->selectLink('Ajouter une affectation')->link();

        $crawler = $this->client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Administration : Créer une affectation');

        $dateStart = new DateTime();
        $dateStart->modify('today');
        $dateEnd = new DateTime();
        $dateEnd->modify('yesterday');
        $form = $crawler->filter('button[value="saveAndAddAnother"]')->form([
            'Assignment[dateStart]' => $dateStart->format('Y-m-d'),
            'Assignment[dateEnd]' => $dateEnd->format('Y-m-d'),
            'Assignment[user]' => $this->collab->getId(),
            'Assignment[restaurant]' => $this->restaurant->getId(),
            'Assignment[fonction]' => $this->fonction->getId(),
        ]);
        $crawler = $this->client->submit($form);

        $this->assertResponseIsUnprocessable();
        $errors = $crawler->filter('.invalid-feedback')->each(
            fn ($node) => trim($node->text())
        );

        $this->assertContains(
            'La date de fin doit être postérieure ou égale à la date de début.',
            $errors
        );

        $this->assertContains(
            'la date de fin doit être postérieure à la date du jour.',
            $errors
        );
    }
}
