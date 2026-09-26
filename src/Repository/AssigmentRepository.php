<?php

namespace App\Repository;

use App\Entity\Assigment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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


    public function findCriteriaAssignments(
        int $limit = 5,
        string $criteria = 'a.dateEnd <= :today',
        array $parameters = ['today' => new \DateTimeImmutable()]): array
    {
        $query = $this->createQueryBuilder('a')
            ->andWhere($criteria)
            ->orderBy('a.dateEnd', 'ASC')
            ->setMaxResults($limit);
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
            ->setParameter('today', new \DateTimeImmutable())
            ->orderBy('a.dateEnd', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Assigment[] Returns an array of Assigment objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Assigment
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
