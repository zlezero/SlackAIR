<?php

namespace App\Controller;

use App\Form\PasswordFormType;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class FirstConnectionController extends AbstractController{

    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    
    /**
     * @Route("/changePassword", name="userChangePassword")
     */
    public function index(Request $request){

        $user = $this->getUser();

        if($user && $user->getFirstConnection()){
            $passwordForm = $this->createForm(PasswordFormType::class, $user);

            $passwordForm->handleRequest($request);

            if ($passwordForm->isSubmitted() && $passwordForm->isValid()){

                $this->getUser()->setPassword($this->passwordEncoder->encodePassword($user, $user->getPassword()));
                $this->getUser()->setFirstConnection(false);

                $user = $this->getUser();
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('app');
            }

            return $this->render('firstConnection.html.twig', [
                "user" => [
                    "pseudo" => $user->getPseudo(),
                ],
                "passwordForm" => $passwordForm->createView()
            ]);
        }
        
        return $this->redirectToRoute('app');
    }
}
