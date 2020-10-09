<?php

namespace App\Repository;

use App\Entity\DepartementId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DepartementId|null find($id, $lockMode = null, $lockVersion = null)
 * @method DepartementId|null findOneBy(array $criteria, array $orderBy = null)
 * @method DepartementId[]    findAll()
 * @method DepartementId[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepartementIdRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DepartementId::class);
    }

    // /**
    //  * @return DepartementId[] Returns an array of DepartementId objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DepartementId
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
