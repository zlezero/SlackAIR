<?php

namespace App\Controller;

use App\Entity\Invitation;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Statut;
use Psr\Log\LoggerInterface;

class WebsocketController extends AbstractController
{

    private LoggerInterface $logger;
    private EntityManager $entityManager;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
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
        
        $emInvitation = $this->entityManager->getRepository(Invitation::class);
        
        if( $user->getStatut()->getName() == "Hors Ligne") {
            $user->setStatut($this->getDoctrine()->getRepository(Statut::class)->findOneBy( array('id' => 1)));
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $em->refresh($user);
        }

        return $this->render('websocket/index.html.twig', [
            'controller_name' => 'WebsocketController',
            'channels' => [
                "publiques" => $emInvitation->getChannelUtilisateur(1, $user->getId()),
                "prives" => $emInvitation->getChannelUtilisateur(2, $user->getId()),
                "dm" => $emInvitation->getDMChannels($user->getId())
            ],
            'user' => [
                "pseudo" => $this->getUser()->getPseudo(),
                "statut" => $this->getUser()->getStatut()->getName(),
                "photo_de_profile" => $this->getUser()->getFileName(),
                "statut_color" => $this->getUser()->getStatut()->getStatusColor()
            ]
        ]);
    }

}
