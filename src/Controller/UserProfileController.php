<?php

namespace App\Controller;

use App\Form\PasswordFormType;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class UserProfileController extends AbstractController
{
    public function getUserInfos(Request $request)
    {
        $form = $this->createForm(UserType::class, null);

        $passwordForm = $this->createForm(PasswordFormType::class, null);
        
        return $this->render('websocket/_profile.html.twig', [
            "user" => [
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
                    ],
            "form" =>  $form->createView(),
            "passwordForm" => $passwordForm->createView()
        ]);
    }
}
