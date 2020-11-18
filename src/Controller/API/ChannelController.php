<?php

namespace App\Controller\API;

use App\Form\UserType;
use App\Form\PasswordFormType;
use App\Entity\User;
use App\Entity\Statut;
use App\Entity\Message;
use App\Entity\Invitation;
use App\Entity\Groupe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/channel", name="user")
 */
class ChannelController extends AbstractController
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/getMessages", name="channel_getMessages")
     */
    public function getMessages(Request $request)
    {

        $channelId = $request->get("channelId");

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
                
                if ($channel->getTypeGroupeId()->getId() == 3) {
                    $dataReponse["message"] = ["channel" => $this->getDoctrine()->getManager()->getRepository(Invitation::class)->getDMChannel($this->getUser()->getId(), $channel->getId())];
                    $dataReponse["message"]["channel"]["isFavorite"] = $channelInvitation->getIsFavorite();
                } else {
                    $dataReponse["message"] = ["channel" => [
                                                    "id" => $channel->getId(),
                                                    "type" => $channel->getTypeGroupeId()->getId(),
                                                    "nom" => $channel->getNom(),
                                                    "description" => $channel->getDescription(),
                                                    "date_creation" => $channel->getDateCreation(),
                                                    "isFavorite" => $channelInvitation->getIsFavorite()
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


}