<?php

// src/EventListener/Error404Listener.php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Error404Listener
{

    public function onKernelException(ExceptionEvent $event)
    {
        if (!$event->getException() instanceof NotFoundHttpException) {
            return;
        }

        $reponse = new RedirectResponse($this->urlGenerator->generate('app'));

        $event->setReponse($reponse); 

    }

}