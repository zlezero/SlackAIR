<?php

// src/EventListener/Error404Listener.php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Error404Listener
{

    /**
     * @author VATHONNE Thomas
     * Redirige vers la page d'accueil lorsque l'URL n'a pas été trouvé
     */
    public function onKernelException(ExceptionEvent $event)
    {
        if (!$event->getException() instanceof NotFoundHttpException) {
            return;
        }

        $reponse = new RedirectResponse($this->urlGenerator->generate('app'));

        $event->setReponse($reponse); 

    }

}