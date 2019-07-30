<?php

namespace App\Repository;

use App\Entity\Pay361WithdrawOrder;
use Dbh\SfCoreBundle\Common\BaseRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Pay361WithdrawOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pay361WithdrawOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pay361WithdrawOrder[]    findAll()
 * @method Pay361WithdrawOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class Pay361WithdrawOrderRepository extends BaseRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Pay361WithdrawOrder::class);
    }

    // /**
    //  * @return Pay361WithdrawOrder[] Returns an array of Pay361WithdrawOrder objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Pay361WithdrawOrder
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
