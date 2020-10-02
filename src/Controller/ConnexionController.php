<?php
// src/Controller/ConnexionController.php
namespace App\Controller;

use App\Entity\Task;
use App\Form\ConnexionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ConnexionController extends AbstractController
{
    /**
     * @Route("/", name="connexion")
     */
    public function new(Request $request)
    {
        
        $form = $this->createForm(ConnexionType::class, null);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();
            return $this->redirectToRoute('app');
        }

        return $this->render('connexion.html.twig', [
            'form' => $form->createView(),
        ]);

    }
}