<?php
// src/Controller/LoginController.php
namespace App\Controller;

use App\Form\LoginType;
use App\Entity\User;
use App\Entity\Statut;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Security;

class LoginController extends AbstractController
{

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @Route("/", name="app_login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {

        if ($this->security->isGranted('ROLE_USER')) {
            if( $this->getUser()->getStatut()->getName() == "Hors Ligne"){
                $this->getUser()->setStatut($this->getDoctrine()->getRepository(Statut::class)->findOneBy( array('id' => 1)));
                $user = $this->getUser();
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                $em->refresh($user);
            }
            return $this->redirectToRoute('app');
        }
        
        $form = $this->createForm(LoginType::class, null);

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login.html.twig', [
            'form' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $error
        ]);

    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(){}

}