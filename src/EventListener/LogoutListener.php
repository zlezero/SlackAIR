<?php

namespace App\EventListener;

use App\Entity\User;
use App\Entity\Statut;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LogoutListener{
    
    private $em;
    private $tokenStorage;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    public function __invoke(LogoutEvent $event){
        if($this->tokenStorage->getToken()->getUser()){
            $emailUser = $this->tokenStorage->getToken()->getUser()->getUsername();
            $user = $this->em->getRepository(User::class)->findOneBy( array('email' => $emailUser));
            $user->setStatut($this->em->getRepository(Statut::class)->findOneBy( array('id' => 2)));
            $this->em->persist($user);
            $this->em->flush();
        }
    }
    
}