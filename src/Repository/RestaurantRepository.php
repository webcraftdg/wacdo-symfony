<?php

namespace App\Repository;

use App\Entity\Restaurant;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Restaurant>
 */
class RestaurantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Restaurant::class);
    }

    public function findForUser(User $user): array
    {
        return match ($user->getRole()) {
            User::ROLE_RETAURANT_OWNER => $this->createQueryBuilder('r')
                ->andWhere('r.owner = :owner')
                ->setParameter('owner', $user)
                ->getQuery()
                ->getResult(),
            default => $this->findAll(),
        };
    }
}
