<?php

namespace App\Tests\Unit;

use App\Entity\Assigment;
use App\Entity\Fonction;
use App\Entity\Restaurant;
use App\Entity\User;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
        $this->assertEquals($owner, $restaurant->getOwner());

        $assignment = (new Assigment())
    }
}
