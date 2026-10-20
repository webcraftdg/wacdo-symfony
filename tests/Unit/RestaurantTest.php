<?php

namespace App\Tests\Unit;

use App\Entity\Restaurant;
use App\Entity\User;
use DateTime;
use PHPUnit\Framework\TestCase;

class RestaurantTest extends TestCase
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

        $restaurant = new Restaurant();
        $restaurant->setAddress('25 Rue du test')
        ->setCity('TestCity')
        ->setName('Mon restaurant')
        ->setZipCode(5125)
        ->setOwner($owner);
        $this->assertSame($owner, $restaurant->getOwner());

        $this->assertEquals('25 Rue du test', $restaurant->getAddress());
        $this->assertEquals('TestCity', $restaurant->getCity());
        $this->assertEquals('Mon restaurant', $restaurant->getName());
        $this->assertEquals(5125, $restaurant->getZipCode());

    }
}
