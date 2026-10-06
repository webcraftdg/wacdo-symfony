<?php

namespace App\DataFixtures;

use App\Entity\Assigment;
use App\Entity\Fonction;
use App\Entity\Restaurant;
use App\Entity\User;
use DateTime;
use DateTimeImmutable;
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

        $ownerRawList = 'owner-{code}@webcraftdg.fr';
        $owners = [];
        $nbOwner = 10;

        for($i =0; $i < $nbOwner; $i++) {
            $code = str_pad((string)$i, 3, '0', STR_PAD_LEFT);
            $email = str_replace('{code}', $code, $ownerRawList);
            $userOwner = new User();
            $userOwner->setEmail($email)
            ->setDateRecruitment(new DateTime())
            ->setIsAdmin(false)
            ->setFirstname(ucFirst($faker->word().'-'.$code))
            ->setLastname(ucFirst($faker->word().'-'.$code))
            ->setPassword($this->userPaswwordHasher->hashPassword($userOwner, 'redcat'))
            ->setRoles([User::ROLE_RETAURANT_OWNER]);
            $manager->persist($userOwner);
            $owners[] = $userOwner;
        }

        $collabRawList =  'collab-{code}@webcraftdg.fr';
        $collabs = [];
        $nbCollab = 150;
        for($i = 0; $i < $nbCollab; $i++) {
            $code = str_pad((string)$i, 3, '0', STR_PAD_LEFT);
            $email = str_replace('{code}', $code, $collabRawList);
            $userCollab = new User();
            $userCollab->setEmail($email)
                ->setDateRecruitment(new DateTime())
                ->setIsAdmin(false)
                ->setFirstname(ucFirst($faker->word().'-'.$code))
                ->setLastname(ucFirst($faker->word().'-'.$code))
                ->setPassword($this->userPaswwordHasher->hashPassword($userCollab, 'redcat'))
                ->setRoles([User::ROLE_COLLAB]);
            $manager->persist($userCollab);
            $collabs[] = $userCollab;
        }

        $fonctions = [
            'Cuisinier',
            'Serveur',
            'Chef de salle',
            'Sommelier',
            'Receptionniste',
            'Chef cuisinier',
            'Commis de cuisine',
            'Plongeur',
            'Maitre D\'hotel',
            'Chef de rang',
            'Barman'

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
            'Mac Donald',
            'Burger King',
            'Pizza Delarte',
            'Pizza Hut',
            'Tour de hanoi',
            'Suchi en folie',
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

        for($i=0; $i < 75; $i++) {
            $dateStart = $faker->dateTimeBetween('-2 years', '+6 months');

            $dateEnd = (clone $dateStart)->modify(
                '+' . random_int(1, 12) . ' months'
            );

            $assigment = new Assigment();

            $assigment
                ->setFonction($fonctColla[array_rand($fonctColla)])
                ->setRestaurant($restos[array_rand($restos)])
                ->setUser($collabs[array_rand($collabs)])
                ->setDateStart(DateTimeImmutable::createFromMutable($dateStart))
                ->setDateEnd(DateTimeImmutable::createFromMutable($dateEnd));

            $manager->persist($assigment);
        }
        $manager->flush();
    }
}
