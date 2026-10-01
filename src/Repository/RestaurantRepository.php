<?php

namespace App\Repository;

use App\Entity\Restaurant;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    public function createBuilderForUser(User $user): QueryBuilder
    {
        return match ($user->getRole()) {
            User::ROLE_RETAURANT_OWNER => $this->createQueryBuilder('r')
                ->andWhere('r.owner = :owner')
                ->setParameter('owner', $user)
                ->orderBy('r.name', 'asc'),
            default => $this->createQueryBuilder('r'),
        };
    }
}
