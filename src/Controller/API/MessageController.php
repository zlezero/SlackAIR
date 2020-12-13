<?php

namespace App\Controller\API;

use App\Entity\Media;
use App\Entity\Message;
use App\Entity\Groupe;
use App\Entity\User;
use App\Entity\Invitation;
use App\Entity\TypeMIME;
use App\Form\UploadFileType;
use App\Form\UpdateMessageType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Gos\Bundle\WebSocketBundle\Pusher\PusherInterface;
use App\Service\FileUploader;
use Gos\Bundle\WebSocketBundle\Pusher\Wamp\WampPusher;

/**
 * @Route("/message", name="message")
 */
class MessageController extends AbstractController
{

    private $pusher;

    public function __construct(PusherInterface $wampPusher)
    {
        $this->pusher = $wampPusher;
    }

    public function getMessageOptions(){
        $updateMessageForm= $this->createForm(UpdateMessageType::class,null);
        return $this->render('websocket/_message_options.html.twig', [
            'updateMessageForm' => $updateMessageForm->createView()
        ]);
    }

    /**
     * @Route("/checkMessageOptions", name="checkMessageOptions")
     */

    public function checkMessageOptions(Request $request) {

        $messageId = $request->get('message_id');
        $channelId = $request->get('channel_id');

        $user = $this->getUser();

        if($messageId && $channelId && $this->getDoctrine()->getRepository(Invitation::class)->isUserInChannel($channelId, $user->getId())) {

            $message = $this->getDoctrine()->getRepository(Message::class)->findOneBy( array('id' => $messageId));

            return new JsonResponse(["statut" => "ok",
                                     "message" => ["estMedia" => $message->getMedia() ? true : false, "userId" => $message->getUserId()->getId()]
                                    ]);

        }

        return new JsonResponse(["statut" => "nok",
                                 "message"=>"Erreur lors de l'obtention des options du message."]);

    }

    /**
     * @Route("/deleteMessage", name="deleteMessage")
     */
    public function deleteMessage(Request $request) {

        $messageId = $request->get('message_id');
        $userId = $request->get('user_id');

        if($messageId) {
            
            $message = $this->getDoctrine()->getRepository(Message::class)->findOneBy(array('id' => $messageId));
            
            if($message->getUserId()->getId() == $userId){

                $message->setEstEfface(true);
                $em = $this->getDoctrine()->getManager();
                $em->persist($message);
                $em->flush();

                return new JsonResponse(["statut" => "ok",
                                         "message" => "Le message a bien été effacé."]);

            }
        }
        
        return new JsonResponse(["statut" => "nok",
                                 "message"=> "Erreur lors de la suppression du message."]);
    }

    /**
     * @Route("/setMessage", name="setMessage")
     */
    public function setMessage(Request $request){

        $messageId = $request->get('message_id');
        $newText = $request->get('new_message');
        $userId = $request->get('user_id');

        if($messageId) {
            
            $message = $this->getDoctrine()->getRepository(Message::class)->findOneBy(array('id' => $messageId));
           
            if($message->getUserId()->getId() == $userId){

                $message->setTexte($newText);
                $message->setEstModifie(true);
                $em = $this->getDoctrine()->getManager();
                $em->persist($message);
                $em->flush();

                return new JsonResponse(["statut" => "ok",
                                         "message" => ["text" => $newText],
                                        ]);

            }
        }
        
        return new JsonResponse(["statut" => "nok",
                                 "message"=> "Erreur lors de la mise à jour du message."]);
    }

     /**
     * @Route("/pinMessage", name="pinMessage")
     */
    public function pinMessage(Request $request) {
        
        $messageId = $request->get('message_id');
        $channelId = $request->get('channel_id');
        $user = $this->getUser();

        if($messageId && $channelId && $this->getDoctrine()->getRepository(Invitation::class)->isUserInChannel($channelId, $user->getId())) {
            
            $message = $this->getDoctrine()->getRepository(Message::class)->findOneBy(array('id' => $messageId));
            $message->setEstEpingle(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();

            return new JsonResponse(["statut" => "ok",
                                     "message" => "Le message a bien été épinglé."]);
        }
        
        return new JsonResponse(["statut" => "nok",
                                 "message" => "Erreur lors de l'épinglage du message."]);
    }

     /**
     * @Route("/unpinMessage", name="unpinMessage")
     */
    public function unpinMessage(Request $request) {
        
        $messageId = $request->get('message_id');
        $channelId = $request->get('channel_id');
        $user = $this->getUser();

        if($messageId && $channelId && $this->getDoctrine()->getRepository(Invitation::class)->isUserInChannel($channelId, $user->getId())) {
            
            $message = $this->getDoctrine()->getRepository(Message::class)->findOneBy( array('id' => $messageId));
            $message->setEstEpingle(false);
            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();

            return new JsonResponse(["statut" => "ok",
                                     "message" => "Le message n'est plus épinglé."]);
        }
        
        return new JsonResponse(["statut" => "nok",
                                 "message"=>"Erreur lors du détachement du message."]);
    }

    /**
     * @Route("/sendMediaMessage", name="sendMediaMessage")
     */
    public function sendMediaMessage(Request $request, FileUploader $fileUploader)
    {
        $user = $this->getUser();
        $file = $request->files->get('upload_file')['file'];
        $groupeId = $request->get('groupe_id');

        $form = $this->createForm(UploadFileType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $groupeId != -1 && $this->getDoctrine()->getRepository(Invitation::class)->isUserInChannel($groupeId, $user->getId())) {

            try {

                $fileUploader->setTargetDirectory($this->getParameter('kernel.project_dir') . "/public/uploads/files");

                $fileUploadResult = $fileUploader->upload($file);
                $fileFileName = $fileUploadResult["fileName"];
                $fileMimeType = $fileUploadResult["fileMimeType"];
                $fileSize = $fileUploadResult["fileSize"];

                // Vérification du Mimetype pour l'obtenir et obtenir le label
                $mimeType = $this->getDoctrine()
                                 ->getRepository(TypeMIME::class)
                                 ->findOneBy([
                                    'TypeMIME' => $fileMimeType
                                   ]);

                if(!$mimeType) {
                    $mimeType = $this->getDoctrine()
                                        ->getRepository(TypeMIME::class)
                                        ->getDefaultTypeMime();
                }

                // Création du média
                $media = new Media();
                $media->setFilename($fileFileName);
                $media->setMimeType($mimeType);
                $media->setSize($fileSize);
    
                $em = $this->getDoctrine()->getManager();
                $em->persist($media);
                $em->flush();
                
                // Création du message 
                $user_entity = $this->getDoctrine()
                                    ->getRepository(User::class)
                                    ->find($user->getId());

                $groupe = $this->getDoctrine()
                                ->getRepository(Groupe::class)
                                ->find($groupeId);

                $newMedia = $this->getDoctrine()
                                ->getRepository(Media::class)
                                ->find($media->getId());

                $message = new Message();
                $message->setTexte("");
                $message->setDateEnvoi(date_create());
                $message->setUserId($user_entity);
                $message->setGroupeId($groupe);
                $message->setEstEfface(false);
                $message->setEstEpingle(false);
                $message->setEstModifie(false);
                $message->setMedia($newMedia);

                $em->persist($message);
                $em->flush();

                $this->getDoctrine()->getRepository(Invitation::class)->addNotification($user_entity->getId(), $groupe->getId());
                $em->refresh($user_entity);

                $this->pusher->push(["data" => ["message" => ["id" => $message->getId()], "type" => "media" ]], "message_topic", ["idChannel" => $groupeId], []);

                return new JsonResponse(["statut" => "ok",
                                        "message" => "Le fichier a bien été ajouté dans la base."]);
                    
            } catch(Exception $error) {
                return new JsonResponse(["statut" => "nok",
                                         "message"=>"Erreur lors l'ajout du fichier."]);
            }

        } else {
            return new JsonResponse(["statut" => "nok",
                                     "message"=> "Fichier non valide ou trop volumineux (8Mo maximum)"]);
        }

    }
}
