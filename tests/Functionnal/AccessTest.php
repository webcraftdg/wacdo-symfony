<?php

namespace App\Tests\Functionnal;

use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AccessTest extends WebTestCase
{
    private User $collab;
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

    public function testKOAccessAdminCollab()
    {

        $this->client->loginUser($this->collab);

        $this->client->request('GET', '/admin');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testKOAccessOwner()
    {

        $this->client->loginUser($this->owner);

        $this->client->request('GET', '/admin');

        $this->assertResponseStatusCodeSame(403);
        $this->client->request('GET', '/home');
        $this->assertResponseStatusCodeSame(403);
        $this->client->request('GET', '/fonctions/accueil');
        $this->assertResponseStatusCodeSame(403);
    }

     public function testKOAccess()
    {

        $this->client->loginUser($this->collab);

        $this->client->request('GET', '/restaurants/accueil');
        $this->assertResponseStatusCodeSame(403);
        $this->client->request('GET', '/home');
        $this->assertResponseStatusCodeSame(403);
        $this->client->request('GET', '/fonctions/accueil');
        $this->assertResponseStatusCodeSame(403);
        $this->client->request('GET', '/utilisateurs/accueil');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testOKAccessAssignement()
    {

        $this->client->loginUser($this->collab);

        $this->client->request('GET', '/affectations/accueil');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Les affectations');
    }

    public function testOKAccessAccount()
    {

        $this->client->loginUser($this->collab);

        $this->client->request('GET', '/mon-compte');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Mes informations');
    }

}
