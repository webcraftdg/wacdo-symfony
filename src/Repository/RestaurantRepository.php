<?php
/**
 * RestaurantRepository.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <david.ghyse@webcraftdg.fr>
 * @version XXX
 * @package App\Repository
 */

namespace App\Repository;

use App\Entity\Restaurant;
use App\Entity\User;
use DateTimeImmutable;
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

    public function createBuilderForUser(User $user, bool $displayArchived = true, ?array $criteria = null): QueryBuilder
    {
        $queryBuilder =  match ($user->getRole()) {
            User::ROLE_RESTAURANT_OWNER => $this->createQueryBuilder('r')
                ->andWhere('r.owner = :owner')
                ->setParameter('owner', $user)
                ->orderBy('r.name', 'asc'),
            default => $this->createQueryBuilder('r'),
        };
        if ($displayArchived === false) {
            $queryBuilder->andWhere('r.dateArchived IS NULL');
        }

        if (isset($criteria['keyword']) === true) {
            $queryBuilder->andWhere($queryBuilder->expr()->orX(
                'r.name LIKE :keyword',
                'r.address LIKE :keyword',
                'r.zipCode LIKE :keyword',
                'r.city LIKE :keyword'
            ))->setParameter('keyword', '%'.$criteria['keyword'].'%');
        }
        return $queryBuilder;
    }

    public function countAssignmentCurrentByRestaurant() : mixed
    {
        $today = new DateTimeImmutable();
        return $this->createQueryBuilder('r')
        ->select('count(a.id) as nb, r.name')
        ->leftJoin('r.assignments', 'a')
        ->andWhere('(a.dateStart <= :today AND a.dateEnd >= :today) OR a.id IS NULL')
        ->orderBy('r.name', 'ASC')->groupBy('r.name')
        ->setParameter('today', $today)->getQuery()->getScalarResult();
    }
}
