<?php

namespace App\Tests\Functionnal;

use App\Entity\Restaurant;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminRestaurantControllerTest extends WebTestCase
{
    private User $admin;
    private User $owner;
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

        $this->entityManager->flush();
        parent::setUp();
    }

    public function testIndex()
    {

        $this->client->loginUser($this->admin);

        $crawler = $this->client->request('GET', '/admin');

        $code = $this->client->getResponse()->getStatusCode();
        $this->assertEquals(200, $code);

        $link = $crawler->selectLink('Les restaurants')->link();

        $this->client->click($link);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Administration : Liste des restaurant');
    }

    public function testSumitComplete()
    {
        $this->client->loginUser($this->admin);

        $crawler = $this->client->request('GET', 'admin');

        $link = $crawler->selectLink('Les restaurants')->link();

        $crawler = $this->client->click($link);

        $link = $crawler->selectLink('Ajouter un restaurant')->link();

        $crawler = $this->client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Administration : Créer un restaurant');

        $form = $crawler->filter('button[value="saveAndAddAnother"]')->form([
            'Restaurant[name]' => 'Mon restaurant',
            'Restaurant[address]' => '32 avenue des potirons',
            'Restaurant[zipCode]' => '84511',
            'Restaurant[city]' => 'La truffe',
            'Restaurant[owner]' => $this->owner->getId(),
        ]);
        $crawler = $this->client->submit($form);

        $this->assertResponseRedirects('/admin/restaurant/creer');

        $restaurant = $this->entityManager
            ->getRepository(Restaurant::class)
            ->findOneBy(['name' => 'Mon restaurant']);

        self::assertNotNull($restaurant);

        $crawler = $this->client->followRedirect();
        //TEST KO
        $form = $crawler->filter('button[value="saveAndReturn"]')->form([
            'Restaurant[name]' => 'Mon restaurant',
            'Restaurant[address]' => '32 avenue des potirons',
            'Restaurant[zipCode]' => '84511',
            'Restaurant[city]' => 'La truffe',
            'Restaurant[owner]' => $this->owner->getId(),
        ]);
        $crawler = $this->client->submit($form);
        $this->assertResponseIsUnprocessable();
        $this->assertSelectorTextContains(
            '.invalid-feedback',
            'Un restaurant avec ce nom existe déjà.'
        );
        //TEST OK en return
        $form = $crawler->filter('button[value="saveAndReturn"]')->form([
            'Restaurant[name]' => 'Mon autre restaurant',
            'Restaurant[address]' => '32 avenue des potirons',
            'Restaurant[zipCode]' => '84511',
            'Restaurant[city]' => 'La truffe',
            'Restaurant[owner]' => $this->owner->getId(),
        ]);
        $crawler = $this->client->submit($form);
        $restaurant = $this->entityManager
            ->getRepository(Restaurant::class)
            ->findOneBy(['name' => 'Mon autre restaurant']);

        self::assertNotNull($restaurant);

        $this->assertResponseRedirects('/admin/restaurant/liste');

    }
}
