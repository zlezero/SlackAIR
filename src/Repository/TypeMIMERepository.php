<?php

namespace App\Repository;

use App\Entity\TypeMIME;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TypeMIME|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeMIME|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeMIME[]    findAll()
 * @method TypeMIME[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeMIMERepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeMIME::class);
    }

    // /**
    //  * @return TypeMIME[] Returns an array of TypeMIME objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TypeMIME
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
