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
        ->setIsAdmin(true)
        ->setLastname('admin')
        ->setPassword($this->userPaswwordHasher->hashPassword($userAdmin, 'redcat'))
        ->setRoles([User::ROLE_ADMIN]);
        $manager->persist($userAdmin);

        $ownerRawList = [
            'owner1@webcraftdg.fr',
            'owner2@webcraftdg.fr',
            'owner3@webcraftdg.fr',
            'owner4@webcraftdg.fr',
        ];
        $owners = [];
        foreach($ownerRawList as $email) {
            $userOwner = new User();
            $userOwner->setEmail($email)
            ->setDateRecruitment(new DateTime())
            ->setIsAdmin(false)
            ->setFirstname(ucFirst($faker->word()))
            ->setLastname(ucFirst($faker->word()))
            ->setPassword($this->userPaswwordHasher->hashPassword($userOwner, 'redcat'))
            ->setRoles([User::ROLE_RETAURANT_OWNER]);
            $manager->persist($userOwner);
            $owners[] = $userOwner;
        }

        $collabRawList = [
            'collab1@webcraftdg.fr',
            'collab2@webcraftdg.fr',
            'collab3@webcraftdg.fr',
            'collab4@webcraftdg.fr',
        ];
        $collabs = [];
        foreach($collabRawList as $email) {
            $userCollab = new User();
            $userCollab->setEmail($email)
                ->setDateRecruitment(new DateTime())
                ->setIsAdmin(false)
                ->setFirstname(ucFirst($faker->word()))
                ->setLastname(ucFirst($faker->word()))
                ->setPassword($this->userPaswwordHasher->hashPassword($userCollab, 'redcat'))
                ->setRoles([User::ROLE_COLLAB]);
            $manager->persist($userCollab);
            $collabs[] = $userCollab;
        }

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
            'Arc de Triomphe',
            'Tour d\'Argent',
            'Tour Eiffel : Panoramique',
            'Fouquets',
        ];
        $restos = [];

        foreach($restaurants as $index => $item) {
            $restaurant = new Restaurant();
            $restaurant->setName($item)
            ->setOwner($owners[$index])
            ->setAddress($faker->sentence(6))
            ->setZipCode($faker->numberBetween(60000, 95000))
            ->setCity($faker->word());
            $manager->persist($restaurant);
            $restos[] = $restaurant;
        }
        for($i=0; $i < 4; $i++) {
            $assigment = new Assigment();
            $assigment->setFonction($fonctColla[array_rand($fonctColla)])
            ->setRestaurant($restos[$i])
            ->setUser($collabs[$i])
            ->setDateStart(new DateTime())
            ->setDateEnd($faker->dateTimeThisMonth());
            $manager->persist($assigment);
            $manager->flush();
        }

    }
}
