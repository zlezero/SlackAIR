<?php

namespace App\Repository;

use App\Entity\TypeGroupe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TypeGroupe|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeGroupe|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeGroupe[]    findAll()
 * @method TypeGroupe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeGroupeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeGroupe::class);
    }

    /**
     * @author ZONCHELLO Sébastien
     * Permet de récupérer la liste de types de groupe hormis le DM
     */
    public function getTypeGroupeExceptDM() {

        $entityManager = $this->getEntityManager();
        
        return $entityManager->createQuery('SELECT typegroupe
                                 FROM App\Entity\TypeGroupe typegroupe
                                 WHERE typegroupe.id != 3
                                ')
                    ->getResult();

    }

}
