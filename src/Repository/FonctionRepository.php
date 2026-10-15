<?php
/**
 * FonctionRepository.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <david.ghyse@webcraftdg.fr>
 * @version XXX
 * @package App\Repository
 */
namespace App\Repository;

use App\Entity\Fonction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Fonction>
 */
class FonctionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fonction::class);
    }

    public function createBuilder(bool $displayArchived = true, ?array $criteria = null): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('f');
        if ($displayArchived === false) {
            $queryBuilder->andWhere('f.dateArchived IS NULL');
        }

        if (isset($criteria['keyword']) === true) {
            $queryBuilder->andWhere($queryBuilder->expr()->orX(
                'f.name LIKE :keyword',
            ))->setParameter('keyword', '%'.$criteria['keyword'].'%');
        }
        return $queryBuilder->orderBy('f.name', 'asc');
    }
}
