<?php

namespace App\Repository;

use App\Entity\Groupe;
use App\Entity\User;
use App\Entity\Invitation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Invitation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invitation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invitation[]    findAll()
 * @method Invitation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvitationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invitation::class);
    }

    // /**
    //  * @return Invitation[] Returns an array of Invitation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
    
    public function getAllUtilisateurChannel(int $idChannel) {

        $entityManager = $this->getEntityManager();
        
        return $entityManager->createQuery('SELECT u
                                            FROM App\Entity\User u
                                            INNER JOIN App\Entity\Invitation i
                                            WHERE i.UserId = u.id AND i.GroupeId = :idChannel
                                ')
                    ->setParameter('idChannel', $idChannel)
                    ->getResult();

    }

    public function getChannelUtilisateur(int $typeChannel, int $idUtilisateur)
    {
        return $this->createQueryBuilder('i')
            ->join('i.GroupeId', 'groupe')
            ->andWhere('i.UserId = :idUtilisateur')
            ->andWhere('groupe.TypeGroupeId = :typeChannel')
            ->setParameter('idUtilisateur', $idUtilisateur)
            ->setParameter('typeChannel', $typeChannel)
            ->getQuery()
            ->getResult()
        ;

    }

    public function getUserChannelInvitation(int $idChannel, int $idUtilisateur)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.UserId = :idUtilisateur')
            ->andWhere('i.GroupeId = :idChannel')
            ->setParameter('idUtilisateur', $idUtilisateur)
            ->setParameter('idChannel', $idChannel)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getNewChannelAdmin(int $idChannel, int $idCurrentAdmin)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.UserId != :idUtilisateur')
            ->andWhere('i.GroupeId = :idChannel')
            ->setParameter('idUtilisateur', $idCurrentAdmin)
            ->setParameter('idChannel', $idChannel)
            ->getQuery()
            ->getResult()
        ;   
    }

    public function isUserInChannel(int $idChannel, int $idUtilisateur) {
        return ($this->createQueryBuilder('i')
                    ->andWhere('i.UserId = :idUtilisateur')
                    ->andWhere('i.GroupeId = :idChannel')
                    ->setParameter('idUtilisateur', $idUtilisateur)
                    ->setParameter('idChannel', $idChannel)
                    ->getQuery()
                    ->getOneOrNullResult()
                ) != NULL;
    }

    public function getDMChannels(int $idUtilisateur) {
        
        $entityManager = $this->getEntityManager();
        $returnObj = array();

        $listeChannelsDM = $this->getChannelUtilisateur(3, $idUtilisateur);

        foreach($listeChannelsDM as $channel) {

            if(!$channel->getGroupeId()->getIsDeleted()){

                $data = $entityManager->createQuery('SELECT utilisateur
                                                 FROM App\Entity\User utilisateur
                                                 INNER JOIN App\Entity\Invitation invitation
                                                 WHERE invitation.UserId != :idUtilisateur AND invitation.GroupeId = :idChannel AND utilisateur.id = invitation.UserId
                                               ')
                                ->setParameter('idChannel', $channel->getGroupeId()->getId())
                                ->setParameter('idUtilisateur', $idUtilisateur)
                                ->getOneOrNullResult();

                $returnObj[] = ["channel" => ["id" => $channel->getGroupeId()->getId(), "isFavorite" => $channel->getIsFavorite(), "isDeleted" => $channel->getGroupeId()->getIsDeleted()], "user" => ["id" => $data->getId(), "pseudo" => $data->getPseudo(), "statut" => $data->getStatut()->getFormattedStatus()]];
            
            }
        
        }

        return $returnObj;

    }

    public function getDMChannel(int $idUtilisateur, int $idChannel) {

        $entityManager = $this->getEntityManager();
            
        $data = $entityManager->createQuery('SELECT utilisateur
                                             FROM App\Entity\User utilisateur
                                             INNER JOIN App\Entity\Invitation invitation
                                             WHERE invitation.UserId != :idUtilisateur AND invitation.GroupeId = :idChannel AND utilisateur.id = invitation.UserId
                                            ')
                            ->setParameter('idChannel', $idChannel)
                            ->setParameter('idUtilisateur', $idUtilisateur)
                            ->getOneOrNullResult();

        if ($data != null) {
            return ["id" => $idChannel, "type" => 3, "user" => ["id" => $data->getId(), "pseudo" => $data->getPseudo(), "statut" => $data->getStatut()->getFormattedStatus()]];;
        } else {
            return null;
        }

    }

    public function isDMChannelExist(int $idUtilisateur1, int $idUtilisateur2) {

        $entityManager = $this->getEntityManager();

        $data = $entityManager->createQuery('SELECT utilisateur
                                             FROM App\Entity\User utilisateur
                                             INNER JOIN App\Entity\Invitation invitation1
                                             INNER JOIN App\Entity\Invitation invitation2
                                             INNER JOIN App\Entity\Groupe groupe
                                             WHERE invitation1.UserId = :idUtilisateur1 AND invitation2.UserId = :idUtilisateur2
                                                   AND invitation1.GroupeId = invitation2.GroupeId
                                                   AND groupe.id = invitation1.GroupeId
                                                   AND groupe.TypeGroupeId = 3
                                            ')
                            ->setParameter('idUtilisateur1', $idUtilisateur1)
                            ->setParameter('idUtilisateur2', $idUtilisateur2)
                            ->getResult();

        return $data != null;

    }

    function addNotification(int $idUtilisateur, int $idChannel) {

        $entityManager = $this->getEntityManager();

        $entityManager->createQuery('UPDATE App\Entity\Invitation i
                                     SET i.NonLus = i.NonLus + 1
                                     WHERE i.UserId != :idUtilisateur AND i.GroupeId = :idChannel')
                      ->setParameter('idUtilisateur', $idUtilisateur)
                      ->setParameter('idChannel', $idChannel)
                      ->getResult();

    }


    
}
