<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Psr\Log\LoggerInterface;

/**
 * @Route("/reset-password")
 */
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    private $resetPasswordHelper;

    public function __construct(LoggerInterface $ConsoleLogger, ResetPasswordHelperInterface $resetPasswordHelper)
    {
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->ConsoleLogger = $ConsoleLogger;
    }

    /**
     * Méthode redéfinie par CORREA Aminata
     * Génère et traite le formulaire de requête de la réinitialistion du mot de passe
     *
     * @Route("", name="app_forgot_password_request", methods={"GET", "POST"})
     * 
     */
    public function request(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processSendingPasswordResetEmail(
                $form->get('email')->getData(),
                $mailer
            );
        }

        return $this->render('reset_password/_request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    /**
     * Méthode redéfinie par CORREA Aminata
     * Page de confirmation après que l'utilisateur ait fait sa requête de réinitialisation du mot de passe
     *
     * @Route("/check-email", name="app_check_email")
     */
    public function checkEmail(): Response
    {
        // We prevent users from directly accessing this page
        if (!$this->canCheckEmail()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/check_email.html.twig', [
            'tokenLifetime' => $this->resetPasswordHelper->getTokenLifetime(),
        ]);
    }

    /**
     * Méthode redéfinie par CORREA Aminata
     * Valide et traite l'URL de réinitialisation lorsque l'utilisateur clique dessus à partir de l'email
     *
     * @Route("/reset/{token}", name="app_reset_password", methods={"GET", "POST"})
     */
    public function reset(Request $request, UserPasswordEncoderInterface $passwordEncoder, string $token = null): Response
    {
        if ($token) {
            // On stocke le token dans une session et on le supprime de l'URL pour éviter que l'URL soit
            // chargé dans un navigateur et qu'il soit potentiellement fuité vers un JavaScript tiers.
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSession();
        if (null === $token) {
            throw $this->createNotFoundException("Nous n'avons pas trouvé un token pour la réinitialisation du mot de passe ni dans l'url ni dans la session.");
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('reset_password_error', sprintf(
                "Un problème est survenu lors de votre demande de réinitialisation - %s",
                $e->getReason()
            ));

            return $this->redirectToRoute('app_login');
        }

        // Le token est valide; autorise l'utilisateur à changer son mot de passe
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Le token de réinitialisation du mot de passe doit être utilisé qu'une seule fois, le supprimer
            $this->resetPasswordHelper->removeResetRequest($token);

            // Coder le mot de passe et le modifier
            $encodedPassword = $passwordEncoder->encodePassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            $user->setPassword($encodedPassword);
            $this->getDoctrine()->getManager()->flush();

            // La session est nettoyée après que le mot de passe ait été changé
            $this->cleanSessionAfterReset();

            $this->addFlash(
                'reset_password_success',
                'Votre mot de passe vient d\'être réinitialisé avec succés. Vous pouvez vous connecter.'
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer): RedirectResponse
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Marquer qu'on a le droit de voir la page app_check_email
        $this->setCanCheckEmailInSession();

        // Ne pas révéler si le compte de l'utilisateur a été trouvé ou pas
        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            // If you want to tell the user why a reset email was not sent, uncomment
            // the lines below and change the redirect to 'app_forgot_password_request'.
            // Caution: This may reveal if a user is registered or not.
            //
            // $this->addFlash('reset_password_error', sprintf(
            //     'Un problème est survenu lors du traitement de votre demande de réinitialisation - %s',
            //     $e->getReason()
            // ));

            return $this->redirectToRoute('app_check_email');
        }

        $email = (new TemplatedEmail())
            ->from(new Address('noreply@slackair.com', 'SlackAiR'))
            ->to($user->getEmail())
            ->subject('SlackAIR - Requête de réinitialisation du mot de passe')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
                'tokenLifetime' => $this->resetPasswordHelper->getTokenLifetime(),
                'user' => [
                    "pseudo" => $user->getPseudo()
                ]
            ])
        ;

        $mailer->send($email);

        return $this->redirectToRoute('app_check_email');
    }
}
