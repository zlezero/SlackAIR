<?php
// src/Controller/LoginController.php
namespace App\Controller\API;

use App\Entity\Groupe;
use App\Entity\Invitation;
use App\Entity\TypeGroupe;
use App\Entity\User;
use App\Form\CreateGroupeType;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Gos\Bundle\WebSocketBundle\Pusher\PusherInterface;

/**
 * @Route("/groupe", name="groupe")
 */
class GroupeController extends AbstractController
{

    private EntityManager $entityManager;
    private $pusher;

    public function __construct(EntityManagerInterface $entityManager, PusherInterface $wampPusher)
    {
        $this->entityManager = $entityManager;
        $this->pusher = $wampPusher;
    }
    
    /**
     * @Route("/create/{typeGroupeId}",requirements={"typeGroupeId"="\d+"}, name="create",methods={"GET"})
     */
    public function create(int $typeGroupeId, Request $request)
    {
        $groupe = new Groupe();

        if($typeGroupeId && $typeGroupeId != 0) {

            $groupe->setTypeGroupeId($this->entityManager
                   ->getRepository(TypeGroupe::class)
                   ->find($typeGroupeId));

            $groupe->setIdProprietaire($this->getUser());
            $form = $this->createForm(CreateGroupeType::class, $groupe);

            return $this->render(
                    'websocket\_create_groupe.html.twig', array('form' => $form->createView(),'groupe'=> $groupe)
            );

        }

    }

    /**
     * @Route("/createGrp", name="create-groupe-post", methods={"POST"})
     */
    public function createGrp(Request $request){

        $groupe = new Groupe();

        $groupe->setTypeGroupeId($this->entityManager
               ->getRepository(TypeGroupe::class)
               ->find(1));

        $form = $this->createForm(CreateGroupeType::class, $groupe);
        $form->submit($request->request->get($form->getName()));

        if ($form->isSubmitted() && $form->isValid()) {

            $groupe = $form->getData();

            $invitations = $request->request->get($form->getName())['invitations'];

            $groupe->setDateCreation(new DateTime());
            $groupe->setIdProprietaire($this->getUser());

            $this->entityManager->persist($groupe);
            $this->entityManager->flush();

            foreach($invitations as $userId) {

                $invitation = new Invitation();

                $invitation->setGroupeId($groupe);
                $invitation->setDate(new DateTime());
                $invitation->setStatut(false);

                $invitation->setUserId($this->entityManager
                                            ->getRepository(User::class)
                                            ->find($userId));
                $invitation->setNonLus(1);
                $invitation->setIsFavorite(false);

                $this->entityManager->persist($invitation);
                $this->entityManager->flush();

                $this->pusher->push(["typeEvent" => "nouveau_channel", "data" => $groupe->getFormattedGroupe()], "privateevent_topic", ["idUser" => $userId], []);

            }

            $invitation = new Invitation();

            // On inscrit l'utilisateur comme membre du groupe
            $invitation->setUserId($this->getUser());
            $invitation->setGroupeId($groupe);
            $invitation->setStatut(true);
            $invitation->setDate(new DateTime());
            $invitation->setNonLus(1);
            $invitation->setIsFavorite(false);

            $this->entityManager->persist($invitation);
            $this->entityManager->flush();

            return new JsonResponse(["statut" => "ok", "message" => ["groupe" => ["id" => $groupe->getId(), "nom" => $groupe->getNom(), "type" => $groupe->getTypeGroupeId()->getId()]]]);

        } else {
            return new JsonResponse(["statut" => "nok",
                                    "message"=> "Une erreur est survenue lors de la création du groupe."]);
        }
    }

    /**
     * @Route("/createDM", name="create-groupe-DM", methods={"POST"})
     */
    public function createDM(Request $request) {

        $userId = $request->get("userId");

        if ($userId && is_numeric($userId)) {

            $userDM = $this->entityManager->getRepository(User::class)->find($userId);

            if ($userDM) {

                if (!$this->entityManager->getRepository(Invitation::class)->isDMChannelExist($this->getUser()->getId(), $userDM->getId())) {

                    $groupe = new Groupe();

                    $groupe->setTypeGroupeId($this->entityManager
                        ->getRepository(TypeGroupe::class)
                        ->find(3));
    
                    $groupe->setNom("DM_" . $this->getUser()->getPseudo() . "_" . $userDM->getPseudo());
                    $groupe->setIdProprietaire($this->getUser());
                    $groupe->setDateCreation(new DateTime());
    
                    $this->entityManager->persist($groupe);
                    $this->entityManager->flush();
    
                    $invitation = new Invitation();
                    $invitation->setGroupeId($groupe);
                    $invitation->setDate(new DateTime());
                    $invitation->setStatut(false);
                    $invitation->setUserId($userDM);
                    $invitation->setNonLus(0);
                    $invitation->setIsFavorite(false);
    
                    $this->entityManager->persist($invitation);
                    $this->entityManager->flush();
        
                    $invitation = new Invitation();
        
                    // On inscrit l'utilisateur comme membre du groupe
                    $invitation->setGroupeId($groupe);
                    $invitation->setDate(new DateTime());
                    $invitation->setStatut(true);
                    $invitation->setUserId($this->getUser());
                    $invitation->setNonLus(0);
                    $invitation->setIsFavorite(false);
        
                    $this->entityManager->persist($invitation);
                    $this->entityManager->flush();
                    
                    $infosChannel = $this->entityManager->getRepository(Invitation::class)->getDMChannel($this->getUser()->getId(), $groupe->getId());

                    $this->pusher->push(["typeEvent" => "nouveau_channel", "data" => array_merge($groupe->getFormattedGroupe(), ["user" => $this->getUser()->getFormattedUser()])], "privateevent_topic", ["idUser" => $userDM->getId()], []);
    
                    return new JsonResponse(["statut" => "ok", "message" => $infosChannel]);

                } else {
                    return new JsonResponse(["statut" => "nok",
                                             "message"=> "Channel DM déjà existant"]);
                }

            } else {
                return new JsonResponse(["statut" => "nok",
                                         "message"=> "Utilisateur inexistant"]);
            }

        } else {
            return new JsonResponse(["statut" => "nok",
                                     "message"=>"Arguments invalides"]);
        }

    }
}