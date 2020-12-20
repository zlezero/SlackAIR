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

    /**
     * @author ZONCHELLO Sébastien
     * Permet de récupérer toutes les notifications de groupe d'un utilisateur
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

    /**
     * @author ZONCHELLO Sébastien
     * Permet de récupérer toutes les notifications de nouveaux messages d'un utilisateur
     */
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

    /**
     * @author ZONCHELLO Sébastien
     * Permet d'ajouter une notification d'un nouveau message non lu par un utilisateur
     */
    function addNotification(int $idUtilisateur, int $idChannel) {

        $entityManager = $this->getEntityManager();
        
        $entityManager->createQuery('UPDATE App\Entity\Notification i
                                        SET i.NbMsg = i.NbMsg + 1, i.DateNotification = :newDat
                                        WHERE i.Utilisateur != :idUtilisateur AND i.Groupe = :idChannel AND i.typeNotification = 2 AND i.NbMsg <= 9 AND i.EstLue = false and i.Utilisateur not in (select u.id from App\Entity\User u where u.DernierGroupe= :idChannel)')
                        ->setParameter('idUtilisateur', $idUtilisateur)
                        ->setParameter('idChannel', $idChannel)
                        ->setParameter('newDat', new DateTime())
                        ->getResult();

        $entityManager->createQuery('UPDATE App\Entity\Notification i
                                        SET i.DateNotification = :newDat
                                        WHERE i.Utilisateur != :idUtilisateur AND i.Groupe = :idChannel AND i.typeNotification = 2 AND i.NbMsg > 9 AND i.EstLue = false and i.Utilisateur not in (select u.id from App\Entity\User u where u.DernierGroupe= :idChannel)')
                        ->setParameter('idUtilisateur', $idUtilisateur)
                        ->setParameter('idChannel', $idChannel)
                        ->setParameter('newDat', new DateTime())
                        ->getResult();

        $entityManager->createQuery('UPDATE App\Entity\Notification i
                                    SET i.NbMsg = 1, i.DateNotification = :newDat,i.EstLue=false
                                    WHERE i.Utilisateur != :idUtilisateur AND i.Groupe = :idChannel AND i.EstLue = true and i.Utilisateur not in (select u.id from App\Entity\User u where u.DernierGroupe= :idChannel)')
                    ->setParameter('idUtilisateur', $idUtilisateur)
                    ->setParameter('idChannel', $idChannel)
                    ->setParameter('newDat', new DateTime())
                    ->getResult();
    }


    /**
     * @author ZONCHELLO Sébastien
     * Permet de compter le nombre de notifications de messages non lus par un utilisateur
     */
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

    /**
     * @author ZONCHELLO Sébastien
     * Permet de compter le nombre de notifications de groupes non lus par un utilisateur
     */
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
    
    /**
     * @author ZONCHELLO Sébastien
     * Permet de vérifier si une notification est lue ou pas
     */
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

    /**
     * @author ZONCHELLO Sébastien
     * Permet de récupérer les notifications d'un groupe d'un utilisateur
     */
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
