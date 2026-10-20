<?php

namespace App\Tests\Unit;

use App\Entity\Fonction;
use PHPUnit\Framework\TestCase;

class FonctionTest extends TestCase
{

    public function testCanCreate()
    {
        $fonction = new Fonction();
        $fonction->setName('Cuisinier');
        $this->assertEquals($fonction->getName(), 'Cuisinier');
    }

}
