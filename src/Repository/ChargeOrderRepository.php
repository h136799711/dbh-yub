<?php

namespace App\Repository;

use App\Entity\ChargeOrder;
use Dbh\SfCoreBundle\Common\BaseRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ChargeOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChargeOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChargeOrder[]    findAll()
 * @method ChargeOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChargeOrderRepository extends BaseRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ChargeOrder::class);
    }

    // /**
    //  * @return ChargeOrder[] Returns an array of ChargeOrder objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ChargeOrder
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
