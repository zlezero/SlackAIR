<?php

namespace App\EventListener;

use App\Entity\User;
use App\Entity\Statut;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Gos\Bundle\WebSocketBundle\Pusher\Wamp\WampPusher;
use Gos\Bundle\WebSocketBundle\Pusher\PusherInterface;

class LogoutListener{
    
    private $em;
    private $tokenStorage;
    private $pusher;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage, PusherInterface $wampPusher)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->pusher = $wampPusher;
    }

    /**
     * @author CORREA Aminata
     * Permet de changer le statut de l'utilisateur en "Déconnecté" lorsque ce dernier se déconnecte de l'appli
     */
    public function __invoke(LogoutEvent $event) {

        if($this->tokenStorage->getToken()->getUser() != null){
            
            $emailUser = $this->tokenStorage->getToken()->getUser()->getUsername();
            
            $user = $this->em->getRepository(User::class)->findOneBy( array('email' => $emailUser));
            $user->setStatut($this->em->getRepository(Statut::class)->findOneBy( array('id' => 2)));
            
            $this->em->persist($user);
            $this->em->flush();

            $this->pusher->push(["typeEvent" => "statutChange", "data" => ["user" => ["id" => $user->getId()], "statut" => $user->getStatut()->getFormattedStatus()]], "userevent_topic", ["idUser" => $user->getId(), "typeEvent" => "statut"], []);

        }

    }
    
}