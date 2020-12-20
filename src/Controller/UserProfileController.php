<?php

namespace App\Controller;

use App\Form\PasswordFormType;
use App\Form\UploadPdpType;
use App\Form\APIFormType;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


class UserProfileController extends AbstractController
{
     /**
     * @author CORREA Aminata
     * Permet de récupérer les infos de l'utilisateur et de génerer les différents formulaires du profil
     */
    public function getUserInfos(Request $request)
    {
        $form = $this->createForm(UserType::class, null);

        $passwordForm = $this->createForm(PasswordFormType::class, null);
        $pdpForm = $this->createForm(UploadPdpType::class, null);
        $apiForm = $this->createForm(APIFormType::class, null);

        return $this->render('websocket/_profile.html.twig', [
            "user" => [
                "email"  => $this->getUser()->getEmail(),
                "prenom" => $this->getUser()->getPrenom(),
                "photo_de_profile" => $this->getUser()->getFileName(),
                "nom"    => $this->getUser()->getNom(),
                "age"    => $this->getUser()->getAge(),
                "pseudo" => $this->getUser()->getPseudo(),
                "statut" => $this->getUser()->getStatut()->getName(),
                "statut_color" => $this->getUser()->getStatut()->getStatusColor(),
                "profession" => $this->getUser()->getProfession(),
                "departement" => [
                    "id" => $this->getUser()->getDepartementId() ? $this->getUser()->getDepartementId()->getId() : null,
                    "nom" => $this->getUser()->getDepartementId() ? $this->getUser()->getDepartementId()->getNom() : null,
                ]
                    ],
            "form" =>  $form->createView(),
            "passwordForm" => $passwordForm->createView(),
            "apiForm" => $apiForm->createView(),
            "pdpForm" => $pdpForm->createView()
        ]);
    }
}
