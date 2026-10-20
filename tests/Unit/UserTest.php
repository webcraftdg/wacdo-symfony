<?php

namespace App\Tests\Unit;

use App\Entity\User;
use DateTime;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{

    public function testCollab()
    {

        $collab = new User();
        $collab
            ->setEmail('collab@test.fr')
            ->setLastname('Test')
            ->setFirstname('Collab')
            ->setDateRecruitment(new DateTime())
            ->setRoles([User::ROLE_COLLAB]);
        $this->assertIsArray($collab->getRoles());
        $this->assertArraysHaveEqualValues([User::ROLE_COLLAB], $collab->getRoles());
    }

    public function testOwner()
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
    }

    public function testAdmin()
    {

        $owner = new User();
        $owner
            ->setEmail('admin@test.fr')
            ->setLastname('admin')
            ->setFirstname('admin')
            ->setDateRecruitment(new DateTime())
            ->setRoles([User::ROLE_ADMIN]);
        $this->assertIsArray($owner->getRoles());
        $this->assertEquals('admin', $owner->getLastname());
        $this->assertEquals('admin', $owner->getFirstname());
        $this->assertArraysHaveEqualValues([User::ROLE_ADMIN], $owner->getRoles());

    }
}
