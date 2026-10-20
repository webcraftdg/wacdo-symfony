<?php

namespace App\Tests\Functionnal;

use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminUserControllerTest extends WebTestCase
{
    private User $admin;
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

        $this->entityManager->flush();
        parent::setUp();
    }

    public function testIndex()
    {

        $this->client->loginUser($this->admin);

        $crawler = $this->client->request('GET', '/admin');

        $code = $this->client->getResponse()->getStatusCode();
        $this->assertEquals(200, $code);

        $link = $crawler->selectLink('Les utilisateurs')->link();

        $this->client->click($link);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Administration : Liste des utilisateurs');
    }

    public function testSumitComplete()
    {
        $this->client->loginUser($this->admin);

        $crawler = $this->client->request('GET', 'admin');

        $link = $crawler->selectLink('Les utilisateurs')->link();

        $crawler = $this->client->click($link);

        $link = $crawler->selectLink('Ajouter un utilisateur')->link();

        $crawler = $this->client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Administration : Créer un utilisateur');

        $form = $crawler->filter('button[value="saveAndAddAnother"]')->form([
            'User[email]' => 'collab@test.fr',
            'User[lastname]' => 'Collab',
            'User[firstname]' => 'Collab',
            'User[role]' => User::ROLE_COLLAB,
            'User[dateRecruitment]' => (new DateTime())->format('Y-m-d'),
            'User[plainPassword][first]' => 'motdepasse',
            'User[plainPassword][second]' => 'motdepasse',
        ]);
        $crawler = $this->client->submit($form);

        $this->assertResponseRedirects('/admin/user/creer');

        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'collab@test.fr']);

        self::assertNotNull($user);

        $crawler = $this->client->followRedirect();
        //TEST KO
        $form = $crawler->filter('button[value="saveAndReturn"]')->form([
            'User[email]' => 'collab@test.fr',
            'User[lastname]' => 'Collab',
            'User[firstname]' => 'Collab',
            'User[role]' => User::ROLE_COLLAB,
            'User[dateRecruitment]' => (new DateTime())->format('Y-m-d'),
            'User[plainPassword][first]' => 'motdepasse',
            'User[plainPassword][second]' => 'motdepasse',
        ]);
        $crawler = $this->client->submit($form);
        $this->assertResponseIsUnprocessable();
        $this->assertSelectorTextContains(
            '.invalid-feedback',
            'Cette Email n\'est pas disponible.'
        );
        //TEST OK en return
        $form = $crawler->filter('button[value="saveAndReturn"]')->form([
            'User[email]' => 'collab2@test.fr',
            'User[lastname]' => 'Collab',
            'User[firstname]' => 'Collab',
            'User[role]' => User::ROLE_COLLAB,
            'User[dateRecruitment]' => (new DateTime())->format('Y-m-d'),
            'User[plainPassword][first]' => 'motdepasse',
            'User[plainPassword][second]' => 'motdepasse',
        ]);
        $crawler = $this->client->submit($form);
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'collab2@test.fr']);

        self::assertNotNull($user);

        $this->assertResponseRedirects('/admin/user/liste');

    }
}
