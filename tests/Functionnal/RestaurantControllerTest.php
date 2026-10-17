<?php

namespace App\Tests\Functionnal;

use App\Entity\Restaurant;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RestaurantControllerTest extends WebTestCase
{
    private User $admin;
    private User $owner;
    private Restaurant $restaurant;
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
        ->setRoles([User::ROLE_RETAURANT_OWNER]);
        $this->owner->setPassword($userPasswordHasher->hashPassword($this->owner, 'motDePasseTest'));

        $this->entityManager->persist($this->owner);

        $this->restaurant = (new Restaurant)
        ->setName('Mon restaurant')
        ->setAddress('25 avenue de provence')
        ->setZipCode(78140)
        ->setCity('Velizy')
        ->setOwner($this->owner)
        ->setDateCreate(new DateTime())
        ->setDateUpdate(new DateTime());

        $this->entityManager->persist($this->restaurant);

        $this->entityManager->flush();
        parent::setUp();
    }

    public function testIndex()
    {

        $this->client->loginUser($this->admin);

        $crawler = $this->client->request('GET', '/home');

        $code = $this->client->getResponse()->getStatusCode();
        $this->assertEquals(200, $code);

        $link = $crawler->selectLink('Restaurants')->link();

        $this->client->click($link);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Les restaurants');
        $this->assertSelectorTextContains('td', 'Mon restaurant');
    }

    public function testUpdate()
    {
        $this->client->loginUser($this->admin);

        $crawler = $this->client->request('GET', '/home');

        $link = $crawler->selectLink('Restaurants')->link();

        $crawler = $this->client->click($link);

        $this->assertSelectorTextContains('td', 'Mon restaurant');

        $link = $crawler->filter('a[title="Éditer"]')->link();

        $crawler = $this->client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Modifier le restaurant');

        $form = $crawler->selectButton('Enregistrer')->form([
            'restaurant[name]' => 'Mon restaurant 2',
        ]);
        $crawler = $this->client->submit($form);

        $this->assertResponseRedirects('/restaurants/accueil');

        $restaurant = $this->entityManager
            ->getRepository(Restaurant::class)
            ->findOneBy(['name' => 'Mon restaurant 2']);

        self::assertNotNull($restaurant);
        $crawler = $this->client->followRedirect();
    }

    public function testDetail()
    {
        $this->client->loginUser($this->admin);

        $crawler = $this->client->request('GET', '/home');

        $link = $crawler->selectLink('Restaurants')->link();

        $crawler = $this->client->click($link);

        $this->assertSelectorTextContains('td', 'Mon restaurant');

        $link = $crawler->filter('a[title="Détail"]')->link();

        $crawler = $this->client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Détail du restaurant');
        $this->assertSelectorTextContains('h2', 'Informations');
        $this->assertSelectorTextContains('dd', 'Mon restaurant');
    }
}
