<?php

namespace App\Tests\Functionnal;

use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends WebTestCase
{
    private User $admin;
    private User $user;
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

        $this->user = (new User)
        ->setEmail('collab@test.fr')
        ->setDateRecruitment(new DateTime())
        ->setLastname('Collab')
        ->setFirstname('Collab')
        ->setRole(User::ROLE_COLLAB);
        $this->user->setPassword($userPasswordHasher->hashPassword($this->user, 'motDePasseTest'));

        $this->entityManager->persist($this->user);

        $this->entityManager->flush();
        parent::setUp();
    }

    public function testIndex()
    {

        $this->client->loginUser($this->admin);

        $crawler = $this->client->request('GET', '/home');

        $code = $this->client->getResponse()->getStatusCode();
        $this->assertEquals(200, $code);

        $link = $crawler->selectLink('Collaborateurs')->link();

        $this->client->click($link);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Les collaborateurs');
        $this->assertSelectorTextContains('td', 'Collab');
    }

    public function testUpdate()
    {
        $this->client->loginUser($this->admin);

        $crawler = $this->client->request('GET', '/home');

        $link = $crawler->selectLink('Collaborateurs')->link();

        $crawler = $this->client->click($link);

        $this->assertSelectorTextContains('h1', 'Les collaborateurs');
        $this->assertSelectorTextContains('td', 'Collab');

        $link = $crawler->filter('a[title="Éditer"]')->link();

        $crawler = $this->client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Modifier un collaborateur');

        $form = $crawler->selectButton('Enregistrer')->form([
            'user_collaborator[email]' => 'collab2@test.fr',
            'user_collaborator[lastname]' => 'Collab2',
        ]);
        $crawler = $this->client->submit($form);

        $this->assertResponseRedirects('/utilisateurs/accueil');

        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'collab2@test.fr']);

        self::assertNotNull($user);
        $crawler = $this->client->followRedirect();
    }

    public function testDetail()
    {
        $this->client->loginUser($this->admin);

        $crawler = $this->client->request('GET', '/home');

        $link = $crawler->selectLink('Collaborateurs')->link();

        $crawler = $this->client->click($link);

        $this->assertSelectorTextContains('h1', 'Les collaborateurs');
        $this->assertSelectorTextContains('td', 'Collab');

        $link = $crawler->filter('a[title="Détail"]')->link();

        $crawler = $this->client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Détail du collaborateur');
        $this->assertSelectorTextContains('h2', 'Informations');
        $this->assertSelectorTextContains('dd', 'Collab');
    }

    public function testCreateOK()
    {
        $this->client->loginUser($this->admin);

        $crawler = $this->client->request('GET', '/home');

        $link = $crawler->selectLink('Collaborateurs')->link();

        $crawler = $this->client->click($link);
        $link = $crawler->selectLink('Créer')->link();

        $crawler = $this->client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer un collaborateur');

         $form = $crawler->selectButton('Enregistrer')->form([
            'user_collaborator[email]' => 'collab2@test.fr',
            'user_collaborator[lastname]' => 'Collab 2',
            'user_collaborator[firstname]' => 'Collab 2',
            'user_collaborator[role]' => User::ROLE_COLLAB,
            'user_collaborator[dateRecruitment]' => (new DateTime())->format('Y-m-d'),
            'user_collaborator[plainPassword][first]' => 'motdepasse',
            'user_collaborator[plainPassword][second]' => 'motdepasse',
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/utilisateurs/accueil');

        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();


        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'collab2@test.fr']);

        self::assertNotNull($user);
        $selector = 'tr#collaborator_'.$user->getId().' > td';
        $this->assertSelectorTextContains($selector, 'Collab 2');
    }


    public function testCreateKO()
    {
        $this->client->loginUser($this->admin);

        $crawler = $this->client->request('GET', '/home');

        $link = $crawler->selectLink('Collaborateurs')->link();

        $crawler = $this->client->click($link);
        $link = $crawler->selectLink('Créer')->link();

        $crawler = $this->client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer un collaborateur');

         $form = $crawler->selectButton('Enregistrer')->form([
            'user_collaborator[email]' => 'collab2@test.fr',
            'user_collaborator[lastname]' => 'Collab 2',
            'user_collaborator[firstname]' => 'Collab 2',
            'user_collaborator[role]' => User::ROLE_COLLAB,
            'user_collaborator[dateRecruitment]' => (new DateTime())->format('Y-m-d'),
            'user_collaborator[plainPassword][first]' => 'motdepasse',
            'user_collaborator[plainPassword][second]' => 'motdepasse',
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/utilisateurs/accueil');

        $crawler = $this->client->followRedirect();
        //TEST KO
        $link = $crawler->selectLink('Créer')->link();

        $crawler = $this->client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer un collaborateur');

         $form = $crawler->selectButton('Enregistrer')->form([
            'user_collaborator[email]' => 'collab2@test.fr',
            'user_collaborator[lastname]' => 'Collab 2',
            'user_collaborator[firstname]' => 'Collab 2',
            'user_collaborator[role]' => User::ROLE_COLLAB,
            'user_collaborator[dateRecruitment]' => (new DateTime())->format('Y-m-d'),
            'user_collaborator[plainPassword][first]' => 'motdepasse',
            'user_collaborator[plainPassword][second]' => 'motdepasse',
        ]);
        $this->client->submit($form);
        $this->assertResponseIsUnprocessable();
        $this->assertSelectorTextContains(
            'ul.text-red-600 > li',
            'Cette Email n\'est pas disponible.'
        );
    }
}
