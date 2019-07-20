<?php

namespace App\Repository;

use App\Entity\UserGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserGroup[]    findAll()
 * @method UserGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserGroupRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserGroup::class);
    }

    public function findGroupsNotJoined($id)
    {
        $ids=$this->createQueryBuilder('ug')
            ->leftJoin('ug.users', 'u')
            ->Where('u.id = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getResult();
        if(empty($ids)){
            return $this->createQueryBuilder('ug')
                ->select('ug')
                ->leftJoin('ug.users', 'u')
                ->getQuery()
                ->getResult();

        }else{
            return $this->createQueryBuilder('ug')
                ->where('ug.id NOT IN (:val)')
                ->setParameter('val',$ids, Connection::PARAM_INT_ARRAY)
                ->getQuery()
                ->getResult();
        }


    }
    public function findGroupsJoined($id)
    {
        return $this->createQueryBuilder('ug')
            ->leftJoin('ug.users', 'u')
            ->Where('u.id = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getResult();
    }
}
