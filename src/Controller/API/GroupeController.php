<?php
// src/Controller/LoginController.php
namespace App\Controller\API;

use App\Entity\Groupe;
use App\Entity\Invitation;
use App\Entity\Notification;
use App\Entity\TypeNotification;
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
        $this->pusher = $wampPusher;
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
            if($typeGroupeId==2){
                
                $users = $this->entityManager->getRepository(User::class)->getAllUsersExceptMe($this->getUser()->getId());

                $arrayReponse = array();

                foreach($users as $user) {
                    $arrayReponse[] = $user->getFormattedUser();
                }

                return $this->render(
                    'websocket\_create_groupe_public.html.twig', array('form' => $form->createView(),'groupe'=> $groupe,"users" => $arrayReponse)
                );
            }else if($typeGroupeId==1){

                return $this->render(
                    'websocket\_create_groupe.html.twig', array('form' => $form->createView(),'groupe'=> $groupe)
                );
            }

        }

    }

    
    /**
     * @Route("/getGroupes", name="getAllUsers")
     */
    public function getGroupes(Request $request) {

        $groupes = $this->entityManager->getRepository(Groupe::class)->getAllGroupes();

        $arrayReponse = array();

        foreach($groupes as $groupe) {
            $arrayReponse[] = $groupe->getFormattedGroupe();
        }

        return new JsonResponse([
            "statut" => "ok",
            "message" => ["groupes" => $arrayReponse]
        ]);

    }

    /**
     * @Route("/createInvit", name="getInvit")
     */
    public function createInvit(Request $request) {
        
        $groupId = $request->get("groupeId");

        $user = $this->entityManager->getRepository(User::class)->find($this->getUser()->getId());

        if ($groupId && is_numeric($groupId)) {

            $groupe = $this->entityManager->getRepository(Groupe::class)->find($groupId);

            if ($groupe) {

                if(!$this->entityManager->getRepository(Invitation::class)->isUserInChannel($groupe->getId(),$this->getUser()->getId())) {
                    
                    $invitation = new Invitation();

                    $invitation->setGroupeId($groupe);
                    $invitation->setDate(new DateTime());
                    $invitation->setStatut(true);
                    $invitation->setUserId($user);
                    $invitation->setIsFavorite(false);

                    $notification = new Notification();

                    $notification->setUtilisateur($user);
                    $notification->setTypeNotification($this->entityManager
                    ->getRepository(TypeNotification::class)
                    ->find(2));

                    $notification->setGroupe($groupe);
                    $notification->setEstLue(true);
                    $notification->setNbMsg(0);
                    $notification->setDateNotification(new Datetime());

                    $this->entityManager->persist($invitation);
                    $this->entityManager->persist($notification);
                    $this->entityManager->flush();

                    $infosChannel = $groupe->getFormattedGroupe();
    
                    return new JsonResponse(["statut" => "ok", "message" => $infosChannel]);
                }

            }

        }

        return new JsonResponse(["statut" => "nok",
                                 "message"=> "Arguments invalides"]);

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
            $groupe->setIsDeleted(false);

            $this->entityManager->persist($groupe);
            $this->entityManager->flush();

            foreach($invitations as $userId) {

                $invitation = new Invitation();

                $invitation->setGroupeId($groupe);
                $invitation->setDate(new DateTime());
                $invitation->setStatut(false);
                $user=$this->entityManager
                ->getRepository(User::class)
                ->find($userId);
                $invitation->setUserId($user);
                $invitation->setIsFavorite(false);
                $notification=new Notification();
                $notification->setUtilisateur($user);
                $notification->setTypeNotification($this->entityManager
                ->getRepository(TypeNotification::class)
                ->find(1));
                $notification->setGroupe($groupe);
                $notification->setEstLue(false);
                $notification->setNbMsg(0);
                $notification->setDateNotification(new Datetime());
                $this->pusher->push(
                    ["typeEvent" => "notifGrp", 
                    "data" => ["user" => [
                        "id" => $this->getUser()->getId()], 
                        "notif"=>[
                            "id" => $notification->getId(), 
                            "groupe" => $groupe->getNom(),
                            "groupeId" => $groupe->getId(),
                            "dateNotif"=>$notification->getDateNotification(),
                            "propGrp"=>$groupe->getIdProprietaire()->getPseudo()
                        ],
                    ],
                    ],
                    "notif_topic", ["idUser" => $userId], []);

                $this->entityManager->persist($invitation);
                $this->entityManager->persist($notification);
                $this->entityManager->flush();

                $this->pusher->push(["typeEvent" => "nouveau_channel", "data" => $groupe->getFormattedGroupe()], "privateevent_topic", ["idUser" => $userId], []);

            }

            $invitation = new Invitation();

            // On inscrit l'utilisateur comme membre du groupe
            $invitation->setUserId($this->getUser());
            $invitation->setGroupeId($groupe);
            $invitation->setStatut(true);
            $invitation->setDate(new DateTime());
            $invitation->setIsFavorite(false);

            
            $notification=new Notification();
            $notification->setUtilisateur($this->getUser());
            $notification->setTypeNotification($this->entityManager
            ->getRepository(TypeNotification::class)
            ->find(2));
            $notification->setGroupe($groupe);
            $notification->setEstLue(true);
            $notification->setNbMsg(0);
            $notification->setDateNotification(new Datetime());

            $this->entityManager->persist($invitation);
            $this->entityManager->persist($notification);
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
                    $groupe->setIsDeleted(false);
    
                    $this->entityManager->persist($groupe);
                    $this->entityManager->flush();
    
                    $invitation = new Invitation();
                    $invitation->setGroupeId($groupe);
                    $invitation->setDate(new DateTime());
                    $invitation->setStatut(false);
                    $invitation->setUserId($userDM);
                    $invitation->setIsFavorite(false);

                    $notification=new Notification();
                    $notification->setUtilisateur($userDM);
                    $notification->setTypeNotification($this->entityManager
                    ->getRepository(TypeNotification::class)
                    ->find(1));
                    $notification->setGroupe($groupe);
                    $notification->setEstLue(false);
                    $notification->setNbMsg(0);
                    $notification->setDateNotification(new Datetime());

                    $this->entityManager->persist($invitation);
                    $this->entityManager->persist($notification);
                    $this->entityManager->flush();
        
                    $this->pusher->push(
                        [
                        "typeEvent" => "notifGrp", 
                        "data" => ["user" => [
                            "id" => $this->getUser()->getId()], 
                            "notif"=>[
                                "id" => $notification->getId(), 
                                "groupe" => $this->getUser()->getPseudo(),
                                "groupeId" => $groupe->getId(),
                                "typeGroupeId" => $groupe->getTypeGroupeId()->getId(),
                                "dateNotif"=>$notification->getDateNotification(),
                                "propGrp"=>$groupe->getIdProprietaire()->getPseudo()
                            ],
                        ],
                        ],
                        "notif_topic", ["idUser" => $userId], []);

                    $invitation = new Invitation();
        
                    // On inscrit l'utilisateur comme membre du groupe
                    $invitation->setGroupeId($groupe);
                    $invitation->setDate(new DateTime());
                    $invitation->setStatut(true);
                    $invitation->setUserId($this->getUser());
                    $invitation->setIsFavorite(false);
        
                    $notification=new Notification();
                    $notification->setUtilisateur($this->getUser());
                    $notification->setTypeNotification($this->entityManager
                    ->getRepository(TypeNotification::class)
                    ->find(2));
                    $notification->setGroupe($groupe);
                    $notification->setEstLue(true);
                    $notification->setNbMsg(0);
                    $notification->setDateNotification(new Datetime());

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