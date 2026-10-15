<?php

namespace App\Tests\Functionnal;

use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthenticationControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $userPasswordHasher = static::getContainer()
        ->get(UserPasswordHasherInterface::class);

        $this->entityManager = static::getContainer()
        ->get(EntityManagerInterface::class);

        $admin = (new User())
        ->setDateCreate(new DateTime())
        ->setDateRecruitment(new DateTime())
        ->setEmail('test@webcrafdg.fr')
        ->setFirstname('Admin')
        ->setLastname('Admin')
        ->setDateUpdate(new DateTime())
        ->setRoles([User::ROLE_ADMIN]);
        $admin->setPassword($userPasswordHasher->hashPassword($admin, 'motDePasseTest'));

        $this->entityManager->persist($admin);
        $this->entityManager->flush();
        parent::setUp();
    }

    public function testAuthenticationKO()
    {

        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'test@webcrafdg.fr',
            '_password' => 'motDePasseTest2',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/login');

        $this->client->followRedirect();

        $this->assertSelectorTextContains('span.error', 'Adresse e-mail ou mot de passe incorrect.');
    }

    public function testAuthenticationOK()
    {

        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'test@webcrafdg.fr',
            '_password' => 'motDePasseTest',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();

        $this->assertResponseRedirects('/home');

        $this->client->followRedirect();
       $this->assertSelectorTextContains('h1', 'Tableau de bord');

    }
}
