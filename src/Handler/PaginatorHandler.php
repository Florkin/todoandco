<?php


namespace App\Handler;


use Doctrine\ORM\Query;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class PaginatorHandler
{
    /**
     * @var PaginatorInterface
     */
    private $pager;

    /**
     * PaginatorHandler constructor.
     * @param PaginatorInterface $pager
     */
    public function __construct(PaginatorInterface $pager)
    {
        $this->pager = $pager;
    }

    /**
     * @param Request $request
     * @param Query $query
     * @return PaginationInterface
     */
    public function paginate(Request $request, Query $query): PaginationInterface
    {
        return $this->pager->paginate(
            $query,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 12)
        );
    }
}