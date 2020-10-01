<?php

namespace App\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/message", name="message")
 */
class MessageController extends AbstractController
{
    /**
     * @Route("/send", name="websocket")
     */
    public function send()
    {
        return new Response(
            '<html><body>Test</body></html>'
        );

        return $this->render('websocket/index.html.twig', [
            'controller_name' => 'WebsocketController',
        ]);
    }
}
