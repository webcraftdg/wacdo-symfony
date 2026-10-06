<?php

namespace App\Helper;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\OffsetPaginator;
use Doctrine\ORM\Tools\Pagination\Window;
use Doctrine\ORM\Tools\Pagination\WindowPage;
use IteratorAggregate;
use Symfony\Component\HttpFoundation\Request;
use Traversable;

class EntityProvider implements IteratorAggregate
{

    private ?WindowPage $page = null;

    public function __construct(
        private QueryBuilder $queryBuilder,
        private Request $request,
        private int $pageSize = 15
    )
    {
    }

    public function getPageNumber() : int
    {
        return $this->request->query->getInt('page', 1);
    }

    public function getPage() : WindowPage
    {
        if ($this->page === null) {
            $this->page = (new OffsetPaginator())
            ->paginate(
                $this->queryBuilder->getQuery(),
                Window::fromPageNumberAndSize($this->getPageNumber(), $this->pageSize)
            );
        }
        return $this->page;
    }

    public function getTotalcount()
    {

        return $this->getPage()->getTotalCount();
    }
    /**
     * iterator
     *
     * @return \Traversable
     */
    public function getIterator(): Traversable
    {
        yield from $this->getPage();
    }

       public function getPageCount(): int
    {
        return $this->getPage()->getPageCount();
    }

    public function getCurrentPage(): int
    {
        return $this->getPage()->getPageNumber();
    }
}
