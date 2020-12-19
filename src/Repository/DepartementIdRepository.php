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

}
