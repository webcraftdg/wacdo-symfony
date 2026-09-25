<?php

namespace App\DataFixtures;

use App\Entity\Assigment;
use App\Entity\Fonction;
use App\Entity\Restaurant;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    public function __construct(private UserPasswordHasherInterface $userPaswwordHasher)
    {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $userAdmin = new User();
        $userAdmin->setEmail('admin@webcraftdg.fr')
        ->setDateRecruitment(new DateTime())
        ->setFirstname('admin')
        ->setLastname('admin')
        ->setPassword($this->userPaswwordHasher->hashPassword($userAdmin, 'redcat'))
        ->setRoles([User::ROLE_ADMIN]);
        $manager->persist($userAdmin);

        $userOwner = new User();
        $userOwner->setEmail('owner@webcraftdg.fr')
        ->setDateRecruitment(new DateTime())
        ->setFirstname('owner')
        ->setLastname('owner')
        ->setPassword($this->userPaswwordHasher->hashPassword($userOwner, 'redcat'))
        ->setRoles([User::ROLE_RETAURANT_OWNER]);
        $manager->persist($userOwner);

        $userCollab = new User();
        $userCollab->setEmail('collab@webcraftdg.fr')
        ->setDateRecruitment(new DateTime())
        ->setFirstname('collab')
        ->setLastname('collab')
        ->setPassword($this->userPaswwordHasher->hashPassword($userCollab, 'redcat'))
        ->setRoles([User::ROLE_COLLAB]);
        $manager->persist($userCollab);

        $fonctions = [
            'Cuisinier',
            'Serveur',
            'Chef de salle',
            'Caviste',
            'Receptionniste'
        ];
        $fonctColla = [];
        foreach($fonctions as $name) {
            $fonction = new Fonction();
            $fonction->setName($name);
            $manager->persist($fonction);
            $fonctColla[] = $fonction;
        }

        $restaurants = [
            'Arc de Thriomphe'
        ];
        $restos = [];

        foreach($restaurants as $item) {
            $restaurant = new Restaurant();
            $restaurant->setName($item)
            ->setAddress($faker->sentence(6))
            ->setZipCode($faker->numberBetween(60000, 95000))
            ->setCity($faker->word());
            $manager->persist($restaurant);
            $restos[] = $restaurant;
        }

        $assigment = new Assigment();
        $assigment->setFonction($fonctColla[array_rand($fonctColla)])
        ->setRestaurant($restos[array_rand($restos)])
        ->setUser($userCollab)
        ->setDateStart($faker->dateTimeThisMonth())
        ->setDateEnd(new DateTime());
        $manager->persist($assigment);
        $manager->flush();
    }
}
