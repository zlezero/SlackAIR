<?php

namespace App\Controller;

use App\Entity\Invitation;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
class WebsocketController extends AbstractController
{

    
    private EntityManager $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @Route("/app", name="app")
     */
    public function index()
    {
        $user = $this->getUser();
        
        $grpsPb=array();

        $grpsPv=array();

        $grpsDM=array();

        foreach($this->entityManager->getRepository(Invitation::class)->findBy(['UserId'=>$user]) as $invit){
            if($invit->getGroupeId()->getTypeGroupeId()->getId()==1){
                array_push($grpsPv,$invit->getGroupeId());
            }else if($invit->getGroupeId()->getTypeGroupeId()->getId()==2){
                array_push($grpsPb,$invit->getGroupeId());
            }else{
                array_push($grpsDM,$invit->getGroupeId());
            }
        }

        return $this->render('websocket/index.html.twig', [
            'controller_name' => 'WebsocketController',
            'user'=>$user,
            'grpsPb'=>$grpsPb,
            'grpsPv'=>$grpsPv,
            'grpsDM'=>$grpsDM,
        ]);
    }
}
