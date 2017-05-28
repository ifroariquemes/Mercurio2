<?php

namespace MercurioBundle\Repository;

use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository implements UserLoaderInterface
{

    public function loadUserByUsername($username)
    {
        return $this->createQueryBuilder('u')
                        ->where('u.email = :email')
                        ->setParameter('email', $username)
                        ->getQuery()
                        ->getOneOrNullResult();
    }

    public function getAll($page = 1)
    {
        $q = $this->createQueryBuilder('u')->orderBy('u.name')->getQuery();
        return Paginator::paginate($q, $page);
    }   

}
