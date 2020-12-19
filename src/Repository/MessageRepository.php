<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * @author VATHONNE Thomas
     * Permet de récupérer tous les messages d'un channel
     */    
    public function getMessages(int $channelId, int $userId, int $messageMin = NULL, int $messageMax = NULL)
    {
        return $this->createQueryBuilder('message')
            ->andWhere('message.GroupeId = :channelId')
            ->andWhere('message.EstEfface = 0')
            ->setParameter('channelId', $channelId)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @author CORREA Aminata
     * Permet de récupérer tous les messages épinglés d'un channel
     */
    public function getPinnedMessages(int $channelId, int $userId, int $messageMin = NULL, int $messageMax = NULL)
    {
        return $this->createQueryBuilder('message')
            ->andWhere('message.GroupeId = :channelId')
            ->andWhere('message.EstEfface = 0')
            ->andWhere('message.EstEpingle = 1')
            ->setParameter('channelId', $channelId)
            ->getQuery()
            ->getResult()
        ;
    }
    
}
