<?php

namespace App\Controller\API;

use App\Form\UpdateChannelType;
use App\Entity\User;
use App\Entity\Notification;
use App\Entity\TypeNotification;
use App\Entity\Message;
use App\Entity\Invitation;
use App\Entity\Groupe;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Gos\Bundle\WebSocketBundle\Pusher\PusherInterface;
use Gos\Bundle\WebSocketBundle\Pusher\Wamp\WampPusher;

/**
 * @Route("/channel", name="user")
 */
class ChannelController extends AbstractController
{
    private $passwordEncoder;
    private $pusher;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, PusherInterface $wampPusher)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->pusher = $wampPusher;
    }

    public function getChannelInfos() {

        $form = $this->createForm(UpdateChannelType::class, null);
        return $this->render('websocket/_channel_infos.html.twig', [
            "form" =>  $form->createView()
        ]);

    }

    /**
     * @author VATHONNE Thomas
     * Renvoie tous les messages d'un channel
     * @Route("/getMessages", name="channel_getMessages")
     */
    public function getMessages(Request $request)
    {

        $channelId = $request->get("channelId");

        //Si l'utilisateur est bien présent dans le channel et que l'id du channel est correct
        if ($channelId && is_numeric($channelId) && $this->getDoctrine()->getManager()->getRepository(Invitation::class)->isUserInChannel($channelId, $this->getUser()->getId())) {

            $messages = $this->getDoctrine()->getManager()->getRepository(Message::class)->getMessages($channelId, $this->getUser()->getId(), $request->get("min") ?? null, $request->get("max") ?? null);
            
            $messageObj = array();

            foreach($messages as $message) {
                $messageObj[] = [
                                    "message" => $message->getTexte(),
                                    "messageId" => $message->getId(),
                                    "pseudo" => $message->getUserId()->getPseudo(),
                                    "photo_de_profile" => $message->getUserId()->getFileName(),
                                    "date" => $message->getDateEnvoi(),
                                    "media" => $message->getMedia() ? $message->getMedia()->getFormattedMedia() : null,
                                    "is_updated" => $message->getEstModifie()
                                ];
            }

            $em = $this->getDoctrine()->getManager();
            $user =$em->getRepository(User::class)->find($this->getUser()->getId());
            $user->setDernierGroupe($this->getDoctrine()->getManager()->getRepository(Groupe::class)->find($channelId));
            
            $em->persist($user);
            $em->flush();

            return new JsonResponse(["statut" => "ok",
                                     "message" => ["messages" => $messageObj]]);

        } else {
            return new JsonResponse(["statut" => "nok",
                                     "message" => "Arguments invalides"]);
        }

    }

    /**
     * @author CORREA Aminata
     * Retourne les messages épinglés d'un channel
     * @Route("/getPinnedMessages", name="channel_getPinnedMessages")
     */
    public function getPinnedMessages(Request $request){

        $channelId = $request->get("channelId");

        if ($channelId && is_numeric($channelId) && $this->getDoctrine()->getManager()->getRepository(Invitation::class)->isUserInChannel($channelId, $this->getUser()->getId())) {

            $messages = $this->getDoctrine()->getManager()->getRepository(Message::class)->getPinnedMessages($channelId, $this->getUser()->getId(), $request->get("min") ?? null, $request->get("max") ?? null);
            
            $messageObj = array();

            foreach($messages as $message) {
                $messageObj[] = [
                                    "message" => $message->getTexte(),
                                    "messageId" => $message->getId(),
                                    "pseudo" => $message->getUserId()->getPseudo(),
                                    "photo_de_profile" => $message->getUserId()->getFileName(),
                                    "date" => $message->getDateEnvoi(),
                                    "media" => $message->getMedia() ? $message->getMedia()->getFormattedMedia() : null,
                                    "is_updated" => $message->getEstModifie()
                                ];
            }

            return new JsonResponse(["statut" => "ok",
                                     "message" => ["messages" => $messageObj]]);

        } else {
            return new JsonResponse(["statut" => "nok",
                                     "message" => "Arguments invalides"]);
        }        
    }

    /**
     * @author VATHONNE Thomas
     * Retourne les informations d'un channel
     * @Route("/getInfos", name="channel_getInfos")
     */
    public function getInfos(Request $request)
    {

        $channelId = $request->get("channelId");

        if ($channelId && is_numeric($channelId) && $this->getDoctrine()->getManager()->getRepository(Invitation::class)->isUserInChannel($channelId, $this->getUser()->getId())) {

            $channel = $this->getDoctrine()->getManager()->getRepository(Groupe::class)->findOneBy(['id' => $channelId]);
            
            if ($channel) {
                
                $dataReponse = ["statut" => "ok"];
                
                $channelInvitation = $this->getDoctrine()->getManager()->getRepository(Invitation::class)->getUserChannelInvitation($channelId, $this->getUser()->getId()); 
                
                if ($channel->getTypeGroupeId()->getId() == 3) { //Si il s'agit d'un DM
                    $dataReponse["message"] = ["channel" => $this->getDoctrine()->getManager()->getRepository(Invitation::class)->getDMChannel($this->getUser()->getId(), $channel->getId())];
                    $dataReponse["message"]["channel"]["isFavorite"] = $channelInvitation->getIsFavorite();
                    $dataReponse["message"]["channel"]["other_contact"] =  $this->getDoctrine()->getManager()->getRepository(User::class)->findOneBy(['id' => $dataReponse["message"]["channel"]["user"]["id"]])->getFormattedUser();
                } else {
                    $dataReponse["message"] = ["channel" => [
                                                    "id" => $channel->getId(),
                                                    "type" => $channel->getTypeGroupeId()->getId(),
                                                    "nom" => $channel->getNom(),
                                                    "description" => $channel->getDescription(),
                                                    "date_creation" => $channel->getDateCreation(),
                                                    "isFavorite" => $channelInvitation->getIsFavorite(),
                                                    "proprietaire" => $channel->getIdProprietaire()->getFormattedUser()
                                              ]
                                    ];
                }

                return new JsonResponse($dataReponse);

            } else {
                return new JsonResponse(["statut" => "nok",
                                         "message" => "Channel inexistant"]);
            }

        } else {
            return new JsonResponse(["statut" => "nok",
                                     "message" => "Arguments invalides"]);
        }

    }

    /**
     * @author VATHONNE Thomas
     * Retourne tous les utilisateurs d'un channel
     * @Route("/getAllUsers", name="channel_getAllUsers")
     */
    public function getAllUsers(Request $request)
    {

        $channelId = $request->get("channelId");

        if ($channelId && is_numeric($channelId) && $this->getDoctrine()->getManager()->getRepository(Invitation::class)->isUserInChannel($channelId, $this->getUser()->getId())) {

            $utilisateurs = $this->getDoctrine()->getManager()->getRepository(Invitation::class)->getAllUtilisateurChannel($channelId);

            $utilisateursObj = array();

            foreach($utilisateurs as $utilisateur) {
                $utilisateursObj[] = [
                                    "id" => $utilisateur->getId(),
                                    "pseudo" => $utilisateur->getPseudo(),
                                    "statut" => $utilisateur->getStatut()->getFormattedStatus()
                                ];
            }
            
            return new JsonResponse(["statut" => "ok",
                                    "message" => ["utilisateurs" => $utilisateursObj]]);

        } else {
            return new JsonResponse(["statut" => "nok",
                                     "message" => "Arguments invalides"]);
        }

    }

     /**
      * @author CORREA Aminata
      * Met à jour les informations d'un channel (Nom et description)
      * @Route("/setChannelInfos", name="channel_setInfos")
      */
    public function setChannelInfos(Request $request) {

        $channelId = $request->request->get('channel_id');
        $userId = $this->getUser()->getId();

        $new_channel = new Groupe();

        $form =  $this->createForm(UpdateChannelType::class, $new_channel);

        $form->submit($request->request->get($form->getName()));

        if ($form->isSubmitted() && $form->isValid()) {

            $channel = $this->getDoctrine()->getManager()->getRepository(Groupe::class)->findOneBy(['id' => $channelId]);
            $channelAdmin = $channel->getIdProprietaire()->getId();

            // Si l'utilisateur est bien le propriétaire du channel
            if($channelAdmin == $userId) {

                $channelChangementNom = $channel->getNom() != $new_channel->getNom();
                $channelChangementDescription = $channel->getDescription() != $new_channel->getDescription();
    
                $channel->setNom($new_channel->getNom());
                $channel->setDescription($new_channel->getDescription());
                
                $em = $this->getDoctrine()->getManager();
                $em->persist($channel);
                $em->flush();
                
                if ($channelChangementNom) {
                    $this->pusher->push(["data" => ["event" => ["type" => "channelTitleUpdate"], "channel" => $channelId, "channelTitre" => $channel->getNom()]], "channelevent_topic", ["idChannel" => $channelId], []);
                }
    
                if ($channelChangementDescription) {
                    $this->pusher->push(["data" => ["event" => ["type" => "channelDescriptionUpdate"], "channel" => $channelId, "channelDescription" => $channel->getDescription()]], "channelevent_topic", ["idChannel" => $channelId], []);
                }
    
                return new JsonResponse(["statut" => "ok",
                    "message" => "Les infos du channel ont été mis à jour avec succès !",
                    "channel" => [
                        "titre" => $channel->getNom(),
                        "description" => $channel->getDescription()
                    ]
                ]);

            }

        }

        return new JsonResponse(["statut" => "nok",
            "message" => "Une erreur est survenue lors de la mise à jour des informations du channel."]);

    }

    /**
     * @author CORREA Aminata
     * Permet de faire quitter un utilisateur d'un channel
     * @Route("/leaveChannel", name="channel_leave")
     */
    public function leaveChannel(Request $request){

        $em = $this->getDoctrine()->getManager();
        $channelId = $request->request->get('channel_id');
        $userId = $this->getUser()->getId();
        $channel = $em->getRepository(Groupe::class)->findOneBy(['id' => $channelId]);
        $channelInvitation = $em->getRepository(Invitation::class)->getUserChannelInvitation($channelId, $userId); 
        $channelAdmin = $channel->getIdProprietaire()->getId();

        if($channelInvitation) {

            if($channelInvitation->getUserId()->getId() == $userId) {

                // Si l'utilisateur est le propriétaire du groupe
                if($channelAdmin == $userId && count($channel->getInvitations()) != 1) {

                    // Nommer un nouvel admin
                    $newAdminInvitation = $em->getRepository(Invitation::class)->getNewChannelAdmin($channelId, $userId)[0];
                    $newAdmin = $newAdminInvitation->getUserId();
                    $channel->setIdProprietaire($newAdmin);
                    $em->persist($channel);
                    $em->flush();
                    
                    // Envoyer une notif au nouvel admin
                    $notification = $em->getRepository(Notification::class)->getNotification($channelId, $newAdminInvitation->getUserId()->getId());
                    $notification->setTypeNotification($em->getRepository(TypeNotification::class)->find(3));
                    $notification->setEstLue(false);
                    $notification->setNbMsg(0);
                    $notification->setDateNotification(new Datetime());
                    $em->persist($notification);
                    $em->flush();
                    $this->pusher->push(
                        ["typeEvent" => "notifAdmin", 
                            "data" => ["user" => [
                                "id" => $newAdmin->getId()], 
                                "notif"=>[
                                    "id" => $notification->getId(), 
                                    "groupe" => $channel->getNom(),
                                    "groupeId" => $channel->getId(),
                                    "dateNotif" => $notification->getDateNotification()
                                ],
                            ],
                        ],
                        "notif_topic", ["idUser" => $newAdmin->getId()], []);

                }

                $em->remove($channelInvitation);
                $em->flush();

                // Supprimer softly le groupe s'il ne reste plus qu'un membre
                $utilisateurs = $em->getRepository(Invitation::class)->getAllUtilisateurChannel($channelId);

                if(count($utilisateurs) == 1) {
                    $channel->setIsDeleted(true);
                    $em->persist($channel);
                    $em->flush();
                }

                $this->pusher->push(["data" => ["event" => ["type" => "userLeave"], "user" => ["id" => $userId], "channel" => ["id" => $channel->getId(), "type" => $channel->getTypeGroupeId()->getId()] ]], "message_topic", ["idChannel" => $channelId], []);

                return new JsonResponse(["statut" => "ok",
                    "message" => "Vous avez bien quitté le channel !"]);

            }
        }

        return new JsonResponse(["statut" => "nok",
            "message" => "Une erreur est survenue lors de la sortie de l'utilisateur."]);

    }

}