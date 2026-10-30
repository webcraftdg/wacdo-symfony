<?php
/**
 * AssignmentRepository.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <david.ghyse@webcraftdg.fr>
 * @version XXX
 * @package App\Repository
 */
namespace App\Repository;

use App\Entity\Assignment;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Helper\CriteriaBuilder;
use App\Helper\CriteriaField;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Assignment>
 */
class AssignmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Assignment::class);
    }

    public function createBuilderforUserWithCriteria(
        User $user,
        ?Restaurant $restaurant = null,
        ?array $criteria = null,
        array $keyWordFields = [
            'u.firstname' => 'LIKE',
            'u.lastname' => 'LIKE',
            'r.name' => 'LIKE',
            'r.city' => 'LIKE',
            'f.name' => 'LIKE',
        ],
        array $orderBy = [
            'a.dateEnd' => 'desc'
        ]
    ) : QueryBuilder
    {
        $criteriaBuilder = new CriteriaBuilder();
        foreach($keyWordFields as $attribute => $operator) {
            $criteriaField = new CriteriaField($attribute, $operator, 'keyword');
            $criteriaBuilder->add($criteriaField);
        }
        $queryBuilder = $this->createBuilderFromUserAndRestaurant(
            user:$user,
            restaurant:$restaurant
        );
        if ($criteria !== null) {
            foreach($criteria as $key => $value) {
                $queryBuilder = $this->parseCriteria(
                    queryBuilder: $queryBuilder,
                    key: $key,
                    criteriaBuilder:$criteriaBuilder,
                    value: $value
                );
            }
        }
        foreach($orderBy as $field => $order) {
            $queryBuilder->orderBy($field, $order);
        }
        return $queryBuilder;
    }

    private function parseCriteria(
        QueryBuilder $queryBuilder,
        string $key,
        CriteriaBuilder $criteriaBuilder,
        mixed $value = null
    ) : QueryBuilder
    {
        if ($value !== null && $value !== '') {
            if ($key === 'keyword') {
                $expr = $criteriaBuilder->getExpression();
                $queryBuilder->andWhere($queryBuilder->expr()->orX(
                    ...$expr
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
        array $parameters = ['today' => new DateTimeImmutable()],
        array $orderBy = ['a.dateEnd' => 'ASC']): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->andWhere($criteria);
        foreach($orderBy as $key => $sort) {
            $queryBuilder->addOrderBy($key, $sort);
        }
        foreach($parameters as $key => $value) {
            $queryBuilder->setParameter($key, $value);
        }
        return $queryBuilder;
    }

    public function countPending() : int
    {
        return (int)$this->findCriteriaAssignments(criteria:'a.dateStart >= :today')
        ->select('count(a.id)')->getQuery()->getSingleScalarResult();
    }

    public function countCurrent() : int
    {
        return (int)$this->findCriteriaAssignments(criteria:'a.dateStart <= :today AND a.dateEnd >= :today')
        ->select('count(a.id)')->getQuery()->getSingleScalarResult();
    }

    public function countFinished() : int
    {
        return (int)$this->findCriteriaAssignments()->select('count(a.id)')->getQuery()->getSingleScalarResult();
    }

    /**
     * create bulder from user and restaurant
     *
     * @param  \App\Entity\User            $user
     * @param  \App\Entity\Restaurant|null $restaurant
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createBuilderFromUserAndRestaurant(User $user, ?Restaurant $restaurant = null): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->join('a.restaurant', 'r')
            ->join('a.user', 'u')
            ->join('a.fonction', 'f');
        $queryBuilder = match ($user->getRole()) {
            User::ROLE_RESTAURANT_OWNER => $queryBuilder
                ->andWhere('r.owner = :owner')
                ->setParameter('owner', $user),
            User::ROLE_COLLAB => $queryBuilder
                ->andWhere('a.user = :user')
                ->setParameter('user', $user),

            default => $queryBuilder,
        };
        if ($restaurant !== null) {
            $queryBuilder->andWhere('a.restaurant =:restaurant')
            ->setParameter('restaurant', $restaurant);
        }

        return $queryBuilder;
    }

    /**
     * hasOverlappingAssignment
     *
     * @param  \App\Entity\User           $user
     * @param  \DateTimeImmutable         $dateStart
     * @param  \DateTimeImmutable|null    $dateEnd
     * @param  \App\Entity\Assignment|null $excludedAssignment
     *
     * @return bool
     */
    public function hasOverlappingAssignment(
        User $user,
        DateTimeImmutable $dateStart,
        DateTimeImmutable $dateEnd,
        ?Assignment $excludedAssignment = null
    ): bool {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.user = :user')
            // L'affectation existante ne doit pas être terminée
            // avant le début de la nouvelle.
            ->andWhere('(a.dateEnd >= :dateStart)')
            ->andWhere('a.dateStart <= :dateEnd')
            ->setParameter('dateEnd', $dateEnd)
            ->setParameter('dateStart', $dateStart)
            ->setParameter('dateEnd', $dateEnd)
            ->setParameter('user', $user);

        //Si il s'agit d'une mis à jour, il faut exclure l'assignment de la requête
        if ($excludedAssignment?->getId() !== null) {
            $qb->andWhere('a.id != :excludedId')
            ->setParameter('excludedId', $excludedAssignment->getId());
        }
        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }
}
