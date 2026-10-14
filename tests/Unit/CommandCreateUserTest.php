<?php

namespace App\Tests\Unit;

use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CommandCreateUSerTest extends KernelTestCase
{

    public function testOnErrorCommand()
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find('app:create-user');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'email' => 'test2@webcraftdg.fr',
            'password' => 'c4wL2wH0C2BydUwG',
            'firstname' => 'Testeur',
            'lastname' => 'Admin',
            'role' => 'ROLE_TEST',
        ]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Les roles autorisés sont "ROLE_ADMIN", "ROLE_COLLAB", "ROLE_RESTAURANT_OWNER"', $output);

        self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find('app:create-user');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'email' => 'test2',
            'password' => 'c4wL2wH0C2BydUwG',
            'firstname' => 'Testeur',
            'lastname' => 'Admin',
            'role' => 'ROLE_ADMIN',
        ]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Des erreurs ont été détectées ==> ', $output);
    }

    public function testOnSuccessCommand()
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find('app:create-user');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'email' => 'testadmin@webcraftdg.fr',
            'password' => 'c4wL2wH0C2BydUwG',
            'firstname' => 'Testeur',
            'lastname' => 'Admin',
            'role' => 'ROLE_ADMIN',
        ]);
        $commandTester->assertCommandIsSuccessful();
        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('UTILISATEUR CREER', $output);
    }
}
