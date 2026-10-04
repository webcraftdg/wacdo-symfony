<?php

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
