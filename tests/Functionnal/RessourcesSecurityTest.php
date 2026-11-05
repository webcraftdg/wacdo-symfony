<?php

namespace App\Tests\Functionnal;

use App\Entity\Assignment;
use App\Entity\Fonction;
use App\Entity\Restaurant;
use App\Entity\User;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RessourcesSecurityTest extends WebTestCase
{
    private User $admin;
    private User $collab;
    private User $collab2;
    private User $owner;
    private User $owner1;
    private Restaurant $restaurant;
    private Fonction $fonction;
    private Assignment $assignment;
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

        $this->owner1 = (new User())
        ->setDateCreate(new DateTime())
        ->setDateRecruitment(new DateTime())
        ->setEmail('owner1@webcrafdg.fr')
        ->setFirstname('Owner1')
        ->setLastname('Owner1')
        ->setDateUpdate(new DateTime())
        ->setRoles([User::ROLE_RESTAURANT_OWNER]);
        $this->owner1->setPassword($userPasswordHasher->hashPassword($this->owner1, 'motDePasseTest'));

        $this->entityManager->persist($this->owner1);



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

        $dateStart = new DateTimeImmutable();
        $dateStart->modify('tomorrow');
        $dateEnd = new DateTimeImmutable();
        $dateEnd->modify('+1 months');

        $this->assignment = (new Assignment)
        ->setFonction($this->fonction)
        ->setRestaurant($this->restaurant)
        ->setUser($this->collab)
        ->setDateStart($dateStart)
        ->setDateEnd($dateEnd);
        $this->entityManager->persist($this->assignment);


        $this->entityManager->flush();
        parent::setUp();
    }

    public function testAcessRestaurantOK()
    {

        $this->client->loginUser($this->owner);

        $crawler = $this->client->request('GET', '/restaurants/accueil');
        $code = $this->client->getResponse()->getStatusCode();
        $this->assertEquals(200, $code);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Les restaurants');
        $this->assertSelectorTextContains('td', 'Mon restaurant');

        $link = $crawler->filter('a[title="Éditer"]')->link();
        $this->client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Modifier le restaurant');
    }

    public function testAcessRestaurantAdminOK()
    {

        $this->client->loginUser($this->admin);
        $crawler = $this->client->request('GET', '/home');
        $code = $this->client->getResponse()->getStatusCode();
        $this->assertEquals(200, $code);
        $link = $crawler->selectLink('Restaurants')->link();
        $this->client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Les restaurants');
        $this->client->request('GET', '/restaurants/'.$this->restaurant->getId().'/detail');
        $this->assertResponseIsSuccessful();
        $this->client->request('GET', '/restaurants/'.$this->restaurant->getId().'/mettre-a-jour');
        $this->assertResponseIsSuccessful();

    }

    public function testAcessOwnerKO()
    {

        $this->client->loginUser($this->owner1);
        $this->client->request('GET', '/restaurants/accueil');
        $code = $this->client->getResponse()->getStatusCode();
        $this->assertEquals(200, $code);
        $this->assertSelectorTextContains('h1', 'Les restaurants');

        $this->client->request('GET', '/restaurants/'.$this->restaurant->getId().'/detail');
        self::assertResponseStatusCodeSame(403);
        $this->client->request('GET', '/restaurants/'.$this->restaurant->getId().'/mettre-a-jour');
        self::assertResponseStatusCodeSame(403);
        $this->client->request('GET', '/affectations/accueil');
        $this->assertResponseIsSuccessful();
        $this->client->request('GET', '/affectations/'.$this->assignment->getId().'/mettre-a-jour');
        self::assertResponseStatusCodeSame(403);
    }

    public function testAcessCollabKO()
    {

        $this->client->loginUser($this->owner1);
        $this->client->request('GET', '/affectations/accueil');
        $code = $this->client->getResponse()->getStatusCode();
        $this->assertEquals(200, $code);
        $this->assertSelectorTextContains('h1', 'Les affectations');

        $this->client->request('GET', '/restaurants/'.$this->restaurant->getId().'/detail');
        self::assertResponseStatusCodeSame(403);
        $this->client->request('GET', '/restaurants/'.$this->restaurant->getId().'/mettre-a-jour');
        self::assertResponseStatusCodeSame(403);
        $this->client->request('GET', '/affectations/accueil');
        $this->assertResponseIsSuccessful();
        $this->client->request('GET', '/affectations/'.$this->assignment->getId().'/mettre-a-jour');
        self::assertResponseStatusCodeSame(403);
    }
}
