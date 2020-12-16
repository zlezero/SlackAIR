<?php

namespace App\Repository;

use DateTime;
use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);

    }

    // /**
    //  * @return Notification[] Returns an array of Notification objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Notification
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    

    
    public function getNotificationsGroupes(int $idUtilisateur)
    {
        return $this->createQueryBuilder('notifications')
            ->where('notifications.EstLue = false')
            ->andWhere('notifications.Utilisateur = :idUtilisateur')
            ->andWhere('notifications.typeNotification != 2')
            ->setParameter('idUtilisateur', $idUtilisateur)
            ->addOrderBy('notifications.DateNotification', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function getNotificationsMessages(int $idUtilisateur)
    {
        return $this->createQueryBuilder('notifications')
            ->where('notifications.EstLue = false')
            ->andWhere('notifications.Utilisateur = :idUtilisateur')
            ->andWhere('notifications.typeNotification = 2')
            ->setParameter('idUtilisateur', $idUtilisateur)
            ->addOrderBy('notifications.DateNotification', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    function addNotification(int $idUtilisateur, int $idChannel) {

        $entityManager = $this->getEntityManager();
        $entityManager->createQuery('UPDATE App\Entity\Notification i
                                        SET i.NbMsg = i.NbMsg + 1, i.DateNotification = :newDat
                                        WHERE i.Utilisateur != :idUtilisateur AND i.Groupe = :idChannel AND i.typeNotification = 2 AND i.NbMsg <= 9 AND i.EstLue =false')
                        ->setParameter('idUtilisateur', $idUtilisateur)
                        ->setParameter('idChannel', $idChannel)
                        ->setParameter('newDat', new DateTime())
                        ->getResult();
        $entityManager->createQuery('UPDATE App\Entity\Notification i
                                        SET i.DateNotification = :newDat
                                        WHERE i.Utilisateur != :idUtilisateur AND i.Groupe = :idChannel AND i.typeNotification = 2 AND i.NbMsg > 9 AND i.EstLue =false')
                        ->setParameter('idUtilisateur', $idUtilisateur)
                        ->setParameter('idChannel', $idChannel)
                        ->setParameter('newDat', new DateTime())
                        ->getResult();
                        
        $entityManager->createQuery('UPDATE App\Entity\Notification i
                                    SET i.NbMsg = 1, i.DateNotification = :newDat,i.EstLue=false
                                    WHERE i.Utilisateur != :idUtilisateur AND i.Groupe = :idChannel AND i.EstLue =true')
                    ->setParameter('idUtilisateur', $idUtilisateur)
                    ->setParameter('idChannel', $idChannel)
                    ->setParameter('newDat', new DateTime())
                    ->getResult();
    }


    
    public function countMsgNotRead(int $idUtilisateur)
    {
        return $this->createQueryBuilder('notifications')
        ->where('notifications.EstLue = false')
        ->andWhere('notifications.Utilisateur = :idUtilisateur')
        ->andWhere('notifications.typeNotification = 2')
        ->setParameter('idUtilisateur', $idUtilisateur)
        ->select('count(notifications.id)')
        ->getQuery()
        ->getResult();
        ;
    }

    
    public function countGrpNotRead(int $idUtilisateur)
    {
        return $this->createQueryBuilder('notifications')
        ->where('notifications.EstLue = false')
        ->andWhere('notifications.Utilisateur = :idUtilisateur')
        ->andWhere('notifications.typeNotification != 2')
        ->setParameter('idUtilisateur', $idUtilisateur)
        ->select('count(notifications.id)')
        ->getQuery()
        ->getResult();
        ;
    }
    
    public function IsNotRead(int $idGrp,int $idUser)
    {
        try{

            return $this->createQueryBuilder('notifications')
            ->select('notifications.EstLue')
            ->where('notifications.Groupe = :idGrp')
            ->andWhere('notifications.Utilisateur = :idUtilisateur')
            ->setParameter('idGrp', $idGrp)
            ->setParameter('idUtilisateur', $idUser)
            ->getQuery()
            ->getOneOrNullResult();
        }catch(Exception $e){
            return false;
        }
    }

    public function getNotification(int $idGrp,int $idUser)
    {
        return $this->createQueryBuilder('notifications')
        ->where('notifications.Groupe = :idGrp')
        ->andWhere('notifications.Utilisateur = :idUtilisateur')
        ->setParameter('idGrp', $idGrp)
        ->setParameter('idUtilisateur', $idUser)
        ->getQuery()
        ->getSingleResult();
    }
}
