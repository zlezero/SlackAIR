<?php

namespace App\Repository;

use App\Entity\TypeNotification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TypeNotification|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeNotification|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeNotification[]    findAll()
 * @method TypeNotification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeNotification::class);
    }

}
