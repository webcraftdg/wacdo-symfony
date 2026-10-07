<?php

namespace App\Repository;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }


    /**
     * findByUserWithRole
     *
     * This function is used on fitlers assignment to filtering users in form search Type
     *
     * @param  \App\Entity\User $user
     *
     * @return QueryBuilder
     */
    public function findByUserWithRole(User $user) : QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $queryBuilder = match ($user->getRole()) {
            User::ROLE_COLLAB => $queryBuilder
                ->andWhere('u = :user')
                ->setParameter('user', $user),
            default => $queryBuilder,
        };
        return $queryBuilder;
    }

    /**
     * create collaborator suary
     *
     * @param  \App\Entity\User|null      $user
     * @param  bool                       $noAffected
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createCollaboratorQuery(
        ?User $user = null,
        bool $noAffected = false,
        ?array $criteria = null) : QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('u');
        if ($user instanceof User) {
            $queryBuilder = match ($user->getRole()) {
                User::ROLE_COLLAB => $queryBuilder
                    ->andWhere('u = :user')
                    ->setParameter('user', $user),
                default => $queryBuilder,
            };
        }
        if ($noAffected === true) {
            $today = new DateTimeImmutable('today');
            $queryBuilder->leftJoin(
                'u.assigments',
                'a',
                Expr\Join::WITH,
                'a.dateStart <= :today AND (a.dateEnd IS NULL OR a.dateEnd >= :today)'
            )->andWhere('a.id IS NULL')
            ->setParameter('today', $today);
        }

        if (isset($criteria['keyword']) === true) {
            $queryBuilder->andWhere($queryBuilder->expr()->orX(
                'u.firstname LIKE :keyword',
                'u.lastname LIKE :keyword',
                'u.email LIKE :keyword'
            ))->setParameter('keyword', '%'.$criteria['keyword'].'%');
        }

        $queryBuilder->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_COLLAB%')
            ->orderBy('u.lastname', 'ASC');
        return $queryBuilder;
    }

    public function countAvailable() : int
    {
        return (int)$this->createCollaboratorQuery(
            noAffected:true
        )->select('count(u.id)')->getQuery()->getSingleScalarResult();
    }

    public function countBusy() : int
    {
        $today = new DateTimeImmutable();
        return (int)$this->createCollaboratorQuery()->select('count(u.id)')->innerJoin(
                'u.assigments',
                'a',
                Expr\Join::WITH,
                'a.dateStart <= :today AND (a.dateEnd IS NULL OR a.dateEnd >= :today)'
            )->setParameter('today', $today)->getQuery()->getSingleScalarResult();
    }


}
