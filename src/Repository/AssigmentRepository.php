<?php

namespace App\Repository;

use App\Entity\Assigment;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Assigment>
 */
class AssigmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Assigment::class);
    }

    public function findforUserWithCriteria(User $user, ?array $criteria = null) : QueryBuilder
    {
        $queryBuilder = $this->findForUser($user);
        if ($criteria !== null) {
            foreach($criteria as $key => $value) {
                $queryBuilder = $this->parseCriteria($key, $queryBuilder, $value);
            }
        }
        return $queryBuilder;
    }

    private function parseCriteria(string $key, QueryBuilder $queryBuilder, mixed $value = null) : QueryBuilder
    {
        if ($value !== null && $value !== '') {
            if ($key === 'keyword') {
                $queryBuilder->andWhere($queryBuilder->expr()->orX(
                    'u.firstname LIKE :keyword',
                    'u.lastname LIKE :keyword',
                    'r.name LIKE :keyword',
                    'r.city LIKE :keyword',
                    'f.name LIKE :keyword',
                ))->setParameter($key, '%'.$value.'%');

            }
            $expression = match($key) {
                'dateStart' => 'a.'.$key.'>=:'.$key,
                'dateEnd' => 'a.'.$key.'<=:'.$key,
                'keyword' => null,
                default =>'a.'.$key.'=:'.$key,
            };
            if ($expression !== null) {
                $queryBuilder->andWhere($expression)->setParameter($key, $value);
            }
        }
        return $queryBuilder;
    }

    public function findCriteriaAssignments(
        string $criteria = 'a.dateEnd <= :today',
        array $parameters = ['today' => new DateTimeImmutable()]): array
    {
        $query = $this->createQueryBuilder('a')
            ->andWhere($criteria)
            ->orderBy('a.dateEnd', 'ASC');
        foreach($parameters as $key => $value) {
            $query->setParameter($key, $value);
        }
        return $query->getQuery()
            ->getResult();

    }

    public function findUpcomingAssignments(int $limit = 5): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.dateEnd >= :today')
            ->setParameter('today', new DateTimeImmutable())
            ->orderBy('a.dateEnd', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * findForUser
     *
     * @param  \App\Entity\User           $user
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findForUser(User $user): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->join('a.restaurant', 'r')
            ->join('a.user', 'u')
            ->join('a.fonction', 'f');
        $queryBuilder = match ($user->getRole()) {
            User::ROLE_RETAURANT_OWNER => $queryBuilder
                ->andWhere('r.owner = :owner')
                ->setParameter('owner', $user),
            User::ROLE_COLLAB => $queryBuilder
                ->andWhere('a.user = :user')
                ->setParameter('user', $user),

            default => $queryBuilder,
        };

        return $queryBuilder;
    }

    /**
     * hasOverlappingAssignment
     *
     * @param  \App\Entity\User           $user
     * @param  \DateTimeImmutable         $dateStart
     * @param  \DateTimeImmutable|null    $dateEnd
     * @param  \App\Entity\Assigment|null $excludedAssignment
     *
     * @return bool
     */
    public function hasOverlappingAssignment(
        User $user,
        DateTimeImmutable $dateStart,
        ?DateTimeImmutable $dateEnd,
        ?Assigment $excludedAssignment = null
    ): bool {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.user = :user')
            ->setParameter('user', $user)
            // L'affectation existante ne doit pas être terminée
            // avant le début de la nouvelle.
            ->andWhere('(a.dateEnd IS NULL OR a.dateEnd >= :dateStart)')
            ->setParameter('dateStart', $dateStart);

        if ($dateEnd !== null) {
            // L'affectation existante doit commencer
            // avant la fin de la nouvelle.
            $qb->andWhere('a.dateStart <= :dateEnd')
                ->setParameter('dateEnd', $dateEnd);
        }
        //Si il s'agit d'une mis à jour, il faut exclure l'assignment de la requête
        if ($excludedAssignment?->getId() !== null) {
            $qb->andWhere('a.id != :excludedId')
            ->setParameter('excludedId', $excludedAssignment->getId());
        }
        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }
}
