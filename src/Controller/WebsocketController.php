<?php

namespace App\Controller;

use App\Entity\Invitation;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Statut;

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

        if($user->getFirstConnection()){
            return $this->redirectToRoute('userChangePassword');
        }
        
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
            
        if( $user->getStatut()->getName() == "Hors Ligne"){
            $user->setStatut($this->getDoctrine()->getRepository(Statut::class)->findOneBy( array('id' => 1)));
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $em->refresh($user);
        }

        return $this->render('websocket/index.html.twig', [
            'controller_name' => 'WebsocketController',
            'grpsPb'=>$grpsPb,
            'grpsPv'=>$grpsPv,
            'grpsDM'=>$grpsDM,
            'user' => [
                "pseudo" => $this->getUser()->getPseudo(),
                "statut" => $this->getUser()->getStatut()->getName(),
                "statut_color" => $this->getUser()->getStatut()->getStatusColor()
            ]
        ]);
    }
}
