<?php

namespace MercurioBundle\Repository;

class Paginator
{

    public static function paginate($dql, $page = 1, $limit = 20)
    {
        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($dql);
        $paginator->getQuery()
                ->setFirstResult($limit * ($page - 1))
                ->setMaxResults($limit);
        return $paginator;
    }

}
