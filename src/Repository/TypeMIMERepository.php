<?php

namespace App\Repository;

use App\Entity\TypeMIME;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TypeMIME|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeMIME|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeMIME[]    findAll()
 * @method TypeMIME[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeMIMERepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeMIME::class);
    }

    /**
     * @author VATHONNE Thomas
     * Permet de récupérer le type mime par défaut d'un média
     */
    public function getDefaultTypeMime(): ?TypeMIME
    {
        $entityManager = $this->getEntityManager();
        return $this->findOneBy(['TypeMIME' => 'text/text']);
    }

}
