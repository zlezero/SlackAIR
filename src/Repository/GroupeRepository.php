<?php

namespace App\Repository;

use App\Entity\Groupe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Groupe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Groupe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Groupe[]    findAll()
 * @method Groupe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Groupe::class);
    }

    /**
     * @author ZONCHELLO Sébastien
     * Permet de récupérer tous les groupes publics de la base de données
     */

    public function getAllGroupes(){

        $entityManager = $this->getEntityManager();
        
        return $entityManager->createQuery('SELECT g FROM App\Entity\Groupe g where g.TypeGroupeId = 1')
                    ->getResult();
    }
}
