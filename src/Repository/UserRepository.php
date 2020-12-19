<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @author VATHONNE Thomas
     * Permet de récupérer tous les autres utlisateurs de la base hormis celui qui est connecté
     */
    public function getAllUsersExceptMe(int $idUser) {

        $entityManager = $this->getEntityManager();
        
        return $entityManager->createQuery('SELECT u
                                 FROM App\Entity\User u
                                 WHERE u.id != :idUser
                                ')
                    ->setParameter('idUser', $idUser)
                    ->getResult();

    }

}
