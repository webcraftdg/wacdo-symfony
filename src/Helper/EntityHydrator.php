<?php
/**
 * EntityHydrator.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <david.ghyse@webcraftdg.fr>
 * @version XXX
 * @package App\Helper
 */
namespace App\Helper;

use Doctrine\ORM\QueryBuilder;
use IteratorAggregate;
use Traversable;

class EntityHydrator implements IteratorAggregate
{
    public function __construct(
        private QueryBuilder $queryBuilder
    )
    {}


    /**
     * iterator
     *
     * @return \Traversable
     */
    public function getIterator(): Traversable
    {
        yield from $this->queryBuilder->getQuery()->toIterable();
    }
}
