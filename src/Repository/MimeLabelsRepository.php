<?php

namespace App\Repository;

use App\Entity\MimeLabels;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MimeLabels|null find($id, $lockMode = null, $lockVersion = null)
 * @method MimeLabels|null findOneBy(array $criteria, array $orderBy = null)
 * @method MimeLabels[]    findAll()
 * @method MimeLabels[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MimeLabelsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MimeLabels::class);
    }

    // /**
    //  * @return MimeLabels[] Returns an array of MimeLabels objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MimeLabels
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
