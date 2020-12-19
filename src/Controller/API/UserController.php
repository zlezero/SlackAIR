<?php

namespace App\Controller\API;

use App\Entity\Invitation;
use App\Form\UserType;
use App\Form\PasswordFormType;
use App\Entity\User;
use App\Entity\Statut;
use App\Form\UploadPdpType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Gos\Bundle\WebSocketBundle\Pusher\PusherInterface;
use App\Service\FileUploader;
use Gos\Bundle\WebSocketBundle\Pusher\Wamp\WampPusher;
use Psr\Log\LoggerInterface;

/**
 * @Route("/user", name="user")
 */
class UserController extends AbstractController
{
    private $passwordEncoder;
    private $pusher;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, PusherInterface $wampPusher, EntityManagerInterface $entityManager, LoggerInterface $ConsoleLogger)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->pusher = $wampPusher;
        $this->entityManager = $entityManager;
        $this->ConsoleLogger = $ConsoleLogger;
    }

    /**
     * @author VATHONNE Thomas
     * Retourne les informations d'un utilisateur
     * @Route("/getInfos", name="userGetInfos")
     */
    public function getInfos()
    {
        return new JsonResponse([
            "email"  => $this->getUser()->getEmail(),
            "prenom" => $this->getUser()->getPrenom(),
            "nom"    => $this->getUser()->getNom(),
            "age"    => $this->getUser()->getAge(),
            "pseudo" => $this->getUser()->getPseudo(),
            "statut" => $this->getUser()->getStatut()->getName(),
            "statut_color" => $this->getUser()->getStatut()->getStatusColor(),
            "profession" => $this->getUser()->getProfession(),
            "departement" => [
                "nom" => $this->getUser()->getDepartementId() ? $this->getUser()->getDepartementId()->getNom(): null,
                "chef" => [
                    "nom" => $this->getUser()->getDepartementId() ? $this->getUser()->getDepartementId()->getIdResponsable()->getNom() : null,
                    "prenom" => $this->getUser()->getDepartementId() ? $this->getUser()->getDepartementId()->getIdResponsable()->getPrenom() : null
                ]
            ]
        ]);
    }

    /**
     * @author CORREA Aminata
     * Permet de modifier les informations d'un utilisateur
     * @Route("/setInfos", name="userSetInfos")
     */
    public function setInfos(Request $request){

        $user = new User();

        $form =  $this->createForm(UserType::class, $user);

        $form->submit($request->request->get($form->getName()));
        
        if ($form->isSubmitted() && $form->isValid()){

            $this->getUser()->setPseudo($user->getPseudo());
            $this->getUser()->setAge($user->getAge());
            $this->getUser()->setProfession($user->getProfession());
            $this->getUser()->setDepartementId($user->getDepartementId());

            $user = $this->getUser();
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $em->refresh($user);

            $this->pusher->push(["typeEvent" => "pseudoChange", "data" => ["user" => ["id" => $this->getUser()->getId(), "pseudo" => $this->getUser()->getPseudo()]]], "userevent_topic", ["idUser" => $this->getUser()->getId()], []);

            return new JsonResponse(["statut" => "ok",
            "message" => "Le profil a été mis à jour avec succès !",
            "user" => [
                "age"    => $user->getAge(),
                "pseudo" => $user->getPseudo(),
                "profession" => $user->getProfession(),
                "departement" => [
                    "nom" => $this->getUser()->getDepartementId() ? $this->getUser()->getDepartementId()->getNom(): null,
                    "chef" => [
                        "nom" => $this->getUser()->getDepartementId() ? $this->getUser()->getDepartementId()->getIdResponsable()->getNom() : null,
                        "prenom" => $this->getUser()->getDepartementId() ? $this->getUser()->getDepartementId()->getIdResponsable()->getPrenom() : null
                    ]
                ]
                    ]
            ]);
        }

        return new JsonResponse(["statut" => "nok",
            "message" => "Une erreur est survenue lors de la mise à jour du profil."]);
    }

    /**
     * @author CORREA Aminata
     * Permet de modifier le mot de passe d'un utilisateur
     * @Route("/setPassword", name="userSetPassword")
     */
    public function setPassword(Request $request) {

        $user = new User();

        $form =  $this->createForm(PasswordFormType::class, $user);

        $form->submit($request->request->get($form->getName()));

        if ($form->isSubmitted() && $form->isValid()) {

            $this->getUser()->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));

            $user = $this->getUser();
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return new JsonResponse(["statut" => "ok",
            "message" => "Mot de passe mis à jour avec succès !"]);

        }
        
        return new JsonResponse(["statut" => "nok",
            "message" => $this->getErrorMessages($form)]);

    }

    /**
     * @author CORREA Aminata
     * Permet de modifier le statut d'un utilisateur
     * @Route("/setStatut", name="userSetStatut")
     */
    public function setStatut(Request $request) {

        $statutId = $request->get("statutId");

        if($statutId && $this->getUser()) {

            $this->getUser()->setStatut($this->getDoctrine()->getRepository(Statut::class)->findOneBy( array('id' => $statutId)));

            $user = $this->getUser();
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $em->refresh($user);

            $this->pusher->push(["typeEvent" => "statutChange", "data" => ["user" => ["id" => $user->getId()], "statut" => $user->getStatut()->getFormattedStatus()]], "userevent_topic", ["idUser" => $user->getId(), "typeEvent" => "statut"], []);

            return new JsonResponse(["statut" => "ok",
            "message" => "Statut mis à jour !",
            "statut" => $user->getStatut()->getFormattedStatus()]);

        }

        return new JsonResponse(["statut" => "nok",
        "message" => ""]);

    }

    /**
     * @author VATHONNE Thomas
     * Retourne les messages d'erreurs d'un formulaire
     */
    public function getErrorMessages($form) {

        $errors = array();

        foreach ($form->getErrors(true, true) as $error) {
            $propertyPath = str_replace(']', '', str_replace('children[', '', $error->getCause()->getPropertyPath()));
            $errors[$propertyPath] = $error->getMessage();
        }
        
        return $errors;
        
    }

    /**
     * @author BABA-AISSA Riad
     * Retourne tout les contacts d'un utilisateur
     * @Route("/getContacts", name="getAllUsers")
     */
    public function getContacts(Request $request) {

        $users = $this->entityManager->getRepository(User::class)->getAllUsersExceptMe($this->getUser()->getId());

        $arrayReponse = array();

        foreach($users as $user) {
            $arrayReponse[] = $user->getFormattedUser();
        }

        return new JsonResponse([
            "statut" => "ok",
            "message" => ["users" => $arrayReponse]
        ]);

    }

    /**
     * @author ZONCHELLO Sébastien et VATHONNE Thomas
     * Permet de changer la photo de profil d'un utilisateur
     * @Route("/setPdp", name="userSetPdp", methods={"POST"})
     */
    public function setPdp(Request $request, FileUploader $fileUploader) {

        $user = $this->getUser();

        try {

            $form = $this->createForm(UploadPdpType::class);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $pdp = $request->files->get('upload_pdp')['pdp'];

                $pdpFileName = $fileUploader->upload($pdp)["fileName"];
                $user->setFileName($pdpFileName);
    
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                $em->refresh($user);
    
                return new JsonResponse(["statut" => "ok",
                                            "message" => ["photo_de_profile" => $user->getFileName()]]);

            } else {
                return new JsonResponse(["statut" => "nok",
                                            "message"=> "Photo non valide ou trop volumineuse (2Mo maximum)"]);
            }

        } catch(Exception $error) {
            return new JsonResponse(["statut" => "nok",
                                        "message"=>"Erreur lors l'ajout de la photo"]);
        }

    }

    /**
     * @author CORREA Aminata
     * Permet de mettre un channel en favori
     * @Route("/setInvitationShortcut", name="setInvitationShortcut")
     */
    public function setInvitationShortcut(Request $request) {
        
        $userId = $this->getUser()->getId();
        $channelId = $request->get("currentChannelId");
        $channelInvitation = $this->entityManager->getRepository(Invitation::class)->getUserChannelInvitation($channelId, $userId); 
        
        if($channelInvitation) {
            
            $channelInvitation->setIsFavorite(true);

            $em = $this->getDoctrine()->getManager();
            $em->persist($channelInvitation);
            $em->flush();
            $em->refresh($channelInvitation);

            $data = "";

            if($channelInvitation->getGroupeTypeId() == 3){
                $data =  $this->entityManager->getRepository(Invitation::class)->getDMChannel($userId, $channelId);
            } else {
                $groupe = $channelInvitation->getGroupeId();
                $data =  [
                    "groupe" => [
                        "id" => $groupe->getId(), 
                        "nom" => $groupe->getNom(), 
                        "type" => $groupe->getTypeGroupeId()->getId()
                    ]
                ];
            }
            
            return new JsonResponse(["statut" => "ok",
                                     "message" => $data]);
        } else {

            return new JsonResponse([
                "statut" => "nok",
                "message" => "Ce channel n'existe pas."
            ]);

        }

    }

    /**
     * @author CORREA Aminata
     * Permet de retirer un channel des favoris
     * @Route("/removeInvitationShortcut", name="removeInvitationShortcut")
     */
    public function removeInvitationShortcut(Request $request) {
        
        $userId = $this->getUser()->getId();
        $channelId = $request->get("currentChannelId");
        $channelInvitation = $this->entityManager->getRepository(Invitation::class)->getUserChannelInvitation($channelId, $userId); 
        
        if($channelInvitation) {
            
            $channelInvitation->setIsFavorite(false);

            $em = $this->getDoctrine()->getManager();
            $em->persist($channelInvitation);
            $em->flush();
            $em->refresh($channelInvitation);

            $data = "";

            if($channelInvitation->getGroupeTypeId() == 3){
                $data =  $this->entityManager->getRepository(Invitation::class)->getDMChannel($userId, $channelId);
            } else {
                $groupe = $channelInvitation->getGroupeId();
                $data =  [
                    "groupe" => [
                        "id" => $groupe->getId(), 
                        "nom" => $groupe->getNom(), 
                        "type" => $groupe->getTypeGroupeId()->getId()
                    ]
                ];
            }
            
            return new JsonResponse(["statut" => "ok",
                                     "message" => $data]);
        } else {
            
            return new JsonResponse([
                "statut" => "nok",
                "message" => "Ce channel n'existe pas."
            ]);

        }

    }

    /**
     * Permet de générer une clé d'API
     * @author VATHONNE Thomas
     * @Route("/generateApiKey", name="generateApiKey")
     */
    public function generateApiKey(Request $request) {

        if ($this->getUser()) {
            
            try {
                
                $erreur = false;

                $user = $this->getUser();

                do {
                    
                    $erreur = false;

                    $user->generateApiToken();
    
                    $entityManager = $this->getDoctrine()->getManager();
    
                    try {
                        $entityManager->persist($user);
                        $entityManager->flush();
                    } catch (Exception $e) {
                        $erreur = true;
                    }

                } while ($erreur); //Tant que l'on a pas une clé API valide

                return new JsonResponse(["statut" => "ok",
                                         "message" => ["user" => ["apiKey" => $this->getUser()->getApiToken()]]]);

            } catch (Exception $error) {
                return new JsonResponse(["statut" => "nok",
                                         "message"=> "Erreur lors de la génération de la clé de l'api"]);
            }

        }

    }

}