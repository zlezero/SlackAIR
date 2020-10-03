<?php

namespace App\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/user", name="user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/getInfos", name="userGetInfos")
     */
    public function send()
    {
        return new JsonResponse([
            "email"  => $this->getUser()->getEmail(),
            "prenom" => $this->getUser()->getPrenom(),
            "nom"    => $this->getUser()->getNom(),
            "age"    => $this->getUser()->getAge(),
            "sexe"   => $this->getUser()->getSexe()
        ]);
    }
}
