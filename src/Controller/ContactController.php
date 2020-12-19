<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ContactController extends AbstractController
{
    /**
     * @author BABA-AISSA Riad
     * Génère le template de l'annuaire des utilisateurs
     */
    public function getContact(Request $request)
    {
        return $this->render('websocket/_contact.html.twig',[]);
    }
}
