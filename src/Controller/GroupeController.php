<?php
// src/Controller/LoginController.php
namespace App\Controller;

use App\Entity\Groupe;
use App\Entity\Invitation;
use App\Entity\TypeGroupe;
use App\Form\CreateGroupeType;
use DateTime;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints\Date;

class GroupeController extends AbstractController
{


    private $ConsoleLogger;
    private EntityManager $entityManager;

    public function __construct(LoggerInterface $ConsoleLogger, EntityManagerInterface $entityManager)
    {
        $this->ConsoleLogger = $ConsoleLogger;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/Groupe/create/{typeGroupeId}",requirements={"typeGroupeId"="\d+"}, name="create-groupe",methods={"GET","POST"})
     */
    public function create(int $typeGroupeId=1, Request $request)
    {
        $groupe = new Groupe();
        $groupe->setTypeGroupeId($this->entityManager
        ->getRepository(TypeGroupe::class)
        ->find($typeGroupeId));
        $form = $this->createForm(CreateGroupeType::class,$data=$groupe);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $groupe=$form->getData();
            $groupe->setDateCreation(new DateTime());
            $groupe->setIdProprietaire($this->getUser());
            $this->entityManager->persist($groupe);
            $this->entityManager->flush();
            $invitation = new Invitation();

            // on inscrit l'utilisateur comme membre du groupe
            $invitation->setUserId($this->getUser());
            $invitation->setGroupeId($groupe);
            $invitation->setStatut(true);
            $invitation->setDate(new DateTime());
            $this->entityManager->persist($invitation);
            $this->entityManager->flush();
            return $this->redirectToRoute('app');
        }
        $groupe->setTypeGroupeId($this->entityManager
            ->getRepository(TypeGroupe::class)
            ->find($typeGroupeId));
        $form = $this->createForm(CreateGroupeType::class,$groupe);
        return $this->render(
            //On affiche  une vue twig simple (pas de head ni rien, donc aucun héritage de template...) qui sera intégrée dans la modale.
            'websocket\modale_groupe.html.twig', array('form' => $form->createView(),'groupe'=>$groupe
            )
        );
    }

}