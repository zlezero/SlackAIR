<?php

namespace App\Controller\API;

use App\Form\UserType;
use App\Form\PasswordFormType;
use App\Entity\User;
use App\Entity\Statut;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user", name="user")
 */
class UserController extends AbstractController
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
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
     * @Route("/setStatut", name="userSetStatut")
     */
    public function setStatut(Request $request){
        $statutId = $request->get("statutId");

        if($statutId && $this->getUser()){
            $this->getUser()->setStatut($this->getDoctrine()->getRepository(Statut::class)->findOneBy( array('id' => $statutId)));
            $user = $this->getUser();
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $em->refresh($user);
            return new JsonResponse(["statut" => "ok",
            "message" => "Statut mis à jour !",
            "statut" => [
                "nom" => $user->getStatut()->getName(),
                "color" => $user->getStatut()->getStatusColor()
            ]]);
        }

        return new JsonResponse(["statut" => "nok",
        "message" => ""]);
    }

    public function getErrorMessages($form)
    {
        $errors = array();

        foreach ($form->getErrors(true, true) as $error) {
            $propertyPath = str_replace(']', '', str_replace('children[', '', $error->getCause()->getPropertyPath()));
            $errors[$propertyPath] = $error->getMessage();
        }
        
        return $errors;
        
    }


}