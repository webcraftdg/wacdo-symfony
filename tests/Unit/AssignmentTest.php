<?php

namespace App\Tests\Unit;

use App\Entity\Assignment;
use App\Entity\Fonction;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Enum\AssignmentStatus;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class AssignmentTest extends TestCase
{

    public function testCanCreate()
    {

        $owner = new User();
        $owner
            ->setEmail('owner@test.fr')
            ->setLastname('Test')
            ->setFirstname('Owner')
            ->setDateRecruitment(new DateTime())
            ->setRoles([User::ROLE_RETAURANT_OWNER]);
        $this->assertIsArray($owner->getRoles());
        $this->assertArraysHaveEqualValues([User::ROLE_RETAURANT_OWNER], $owner->getRoles());

        $collab = new User();
        $collab
            ->setEmail('collab@test.fr')
            ->setLastname('Test')
            ->setFirstname('Collab')
            ->setDateRecruitment(new DateTime())
            ->setRoles([User::ROLE_COLLAB]);
        $this->assertIsArray($collab->getRoles());
        $this->assertArraysHaveEqualValues([User::ROLE_COLLAB], $collab->getRoles());

        $fonction = new Fonction();
        $fonction->setName('Cuisinier');
        $this->assertEquals($fonction->getName(), 'Cuisinier');

        $restaurant = new Restaurant();
        $restaurant->setAddress('25 Rue du test')
        ->setCity('TestCity')
        ->setName('Mon restaurant')
        ->setZipCode(5125)
        ->setOwner($owner);
        $this->assertSame($owner, $restaurant->getOwner());

        $dateStart = new DateTime();
        $dateStart->modify('tomorrow');
        $dateEnd = new DateTime();
        $dateEnd->modify('+6 months');
        $assignment = new Assignment();
        $assignment->setFonction($fonction)
        ->setRestaurant($restaurant)
        ->setUser($collab)
        ->setDateStart(DateTimeImmutable::createFromMutable($dateStart))
        ->setDateEnd(DateTimeImmutable::createFromMutable($dateEnd));
        $this->assertSame(AssignmentStatus::EN_ATTENTE, $assignment->getStatus());
    }

    public function testEnAttente()
    {

        $owner = new User();
        $owner
            ->setEmail('owner@test.fr')
            ->setLastname('Test')
            ->setFirstname('Owner')
            ->setDateRecruitment(new DateTime())
            ->setRoles([User::ROLE_RETAURANT_OWNER]);
        $this->assertIsArray($owner->getRoles());
        $this->assertArraysHaveEqualValues([User::ROLE_RETAURANT_OWNER], $owner->getRoles());

        $collab = new User();
        $collab
            ->setEmail('collab@test.fr')
            ->setLastname('Test')
            ->setFirstname('Collab')
            ->setDateRecruitment(new DateTime())
            ->setRoles([User::ROLE_COLLAB]);
        $this->assertIsArray($collab->getRoles());
        $this->assertArraysHaveEqualValues([User::ROLE_COLLAB], $collab->getRoles());

        $fonction = new Fonction();
        $fonction->setName('Cuisinier');
        $this->assertEquals($fonction->getName(), 'Cuisinier');

        $restaurant = new Restaurant();
        $restaurant->setAddress('25 Rue du test')
        ->setCity('TestCity')
        ->setName('Mon restaurant')
        ->setZipCode(5125)
        ->setOwner($owner);
        $this->assertSame($owner, $restaurant->getOwner());

        $dateStart = new DateTime();
        $dateStart->modify('tomorrow');
        $dateEnd = new DateTime();
        $dateEnd->modify('+6 months');
        $assignment = new Assignment();
        $assignment->setFonction($fonction)
        ->setRestaurant($restaurant)
        ->setUser($collab)
        ->setDateStart(DateTimeImmutable::createFromMutable($dateStart))
        ->setDateEnd(DateTimeImmutable::createFromMutable($dateEnd));
        $this->assertSame(AssignmentStatus::EN_ATTENTE, $assignment->getStatus());

    }


    public function testEnCours()
    {

        $owner = new User();
        $owner
            ->setEmail('owner@test.fr')
            ->setLastname('Test')
            ->setFirstname('Owner')
            ->setDateRecruitment(new DateTime())
            ->setRoles([User::ROLE_RETAURANT_OWNER]);
        $this->assertIsArray($owner->getRoles());
        $this->assertArraysHaveEqualValues([User::ROLE_RETAURANT_OWNER], $owner->getRoles());

        $collab = new User();
        $collab
            ->setEmail('collab@test.fr')
            ->setLastname('Test')
            ->setFirstname('Collab')
            ->setDateRecruitment(new DateTime())
            ->setRoles([User::ROLE_COLLAB]);
        $this->assertIsArray($collab->getRoles());
        $this->assertArraysHaveEqualValues([User::ROLE_COLLAB], $collab->getRoles());

        $fonction = new Fonction();
        $fonction->setName('Cuisinier');
        $this->assertEquals($fonction->getName(), 'Cuisinier');

        $restaurant = new Restaurant();
        $restaurant->setAddress('25 Rue du test')
        ->setCity('TestCity')
        ->setName('Mon restaurant')
        ->setZipCode(5125)
        ->setOwner($owner);
        $this->assertSame($owner, $restaurant->getOwner());

        $dateStart = new DateTime();
        $dateStart->modify('yesterday');
        $dateEnd = (clone $dateStart)->modify('+1 months');
        $assignment = new Assignment();
        $assignment->setFonction($fonction)
        ->setRestaurant($restaurant)
        ->setUser($collab)
        ->setDateStart(DateTimeImmutable::createFromMutable($dateStart))
        ->setDateEnd(DateTimeImmutable::createFromMutable($dateEnd));
        $this->assertSame(AssignmentStatus::EN_COURS, $assignment->getStatus());
    }


    public function testFini()
    {

        $owner = new User();
        $owner
            ->setEmail('owner@test.fr')
            ->setLastname('Test')
            ->setFirstname('Owner')
            ->setDateRecruitment(new DateTime())
            ->setRoles([User::ROLE_RETAURANT_OWNER]);
        $this->assertIsArray($owner->getRoles());
        $this->assertArraysHaveEqualValues([User::ROLE_RETAURANT_OWNER], $owner->getRoles());

        $collab = new User();
        $collab
            ->setEmail('collab@test.fr')
            ->setLastname('Test')
            ->setFirstname('Collab')
            ->setDateRecruitment(new DateTime())
            ->setRoles([User::ROLE_COLLAB]);
        $this->assertIsArray($collab->getRoles());
        $this->assertArraysHaveEqualValues([User::ROLE_COLLAB], $collab->getRoles());

        $fonction = new Fonction();
        $fonction->setName('Cuisinier');
        $this->assertEquals($fonction->getName(), 'Cuisinier');

        $restaurant = new Restaurant();
        $restaurant->setAddress('25 Rue du test')
        ->setCity('TestCity')
        ->setName('Mon restaurant')
        ->setZipCode(5125)
        ->setOwner($owner);
        $this->assertSame($owner, $restaurant->getOwner());

        $dateStart = new DateTime();
        $dateStart->modify('-2 months');
        $dateEnd = new DateTime();
        $dateEnd->modify('-1 months')->format('Y-m-d');
        $assignment = new Assignment();
        $assignment->setFonction($fonction)
        ->setRestaurant($restaurant)
        ->setUser($collab)
        ->setDateStart(DateTimeImmutable::createFromMutable($dateStart))
        ->setDateEnd(DateTimeImmutable::createFromMutable($dateEnd));
        $this->assertSame(AssignmentStatus::FINI, $assignment->getStatus());

    }
}
