<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ForgotPasswordType;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use App\Service\SendEmail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class ForgotPasswordController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    private SessionInterface $session;

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository, SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->session       = $session;
        $this->userRepository= $userRepository;
    }

    /**
     * @Route("/forgot-password", name="app_forgot_password", methods={"GET","POST"})
     */
    public function sendRecoveryLink(Request $request, SendEmail $sendEmail, TokenGeneratorInterface $tokenGenerator): Response
    {
        $form= $this->createForm(ForgotPasswordType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->userRepository->findOneBy(
                ['email' => $form['email']->getData()
            ]);

            /* Nous faisons un leurre au cas où il n'y pas d'utilisateur */

            if(!$user){ 
                $this->addFlash('danger', "L'utilisateur n'existe pas");
                return $this->redirectToRoute('account_login');
            }

            $user->setForgotPasswordToken($tokenGenerator->generateToken());

            $this->entityManager->flush();

            $sendEmail->send([
                'recipient_email' => $user->getEmail(),
                'subject' => "Modification de votre mot de passe",
                "html_template" => 'forgot_password/forgot_password_email.html.twig',
                'context' => [
                    'user' => $user
                ]
            ]);
            $this->addFlash('success', "Un e-mail de réinitialisation de mot de passe a été envoyé");
            return $this->redirectToRoute('account_login');
        }

        return $this->render('forgot_password/forgot_password_step_1.html.twig', [
            'forgotPasswordFormStep1' => $form->createView(),
        ]);
    }
    /**
     * @Route("/forgot-password/{id<\d+>}/{token}", name="app_retrieve_credentials", methods={"GET"})
     *
     */
    public function retrieveCredentialsFromTheUrl($token, User $user): RedirectResponse
    {
        $this->session->set('Reset-Password-Token-URL', $token);

        $this->session->set('Reset-Password-User-Email', $user->getEmail());

        return $this->redirectToRoute('app_reset_password');
    }

    /**
     *
     * @Route("/reset-password", name="app_reset_password", methods={"GET","POST"})
     */

    public function resetPasswword(Request $request, UserPasswordEncoderInterface $encoder)
    {
        [
            'token'=> $token,
            'userEmail' => $userEmail
        ]= $this->getCredentialsFromSession();

        $user = $this->userRepository->findOneBy(['email' => $userEmail ]);

        if (!$user) {
            return $this->redirectToRoute('app_forgot_password');
        }
        /** Si le token de l'utilisateur ou si le token passé dans l'url est différent de son token en base de donnée, alors on le renvoie vers app_forgot_password */
        if (($user->getForgotPasswordToken() === null) || $user->getForgotPasswordToken()!== $token) {
            return $this->redirectToRoute('app_forgot_password');
        }
        $form = $this->createForm(ResetPasswordType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setHash($encoder->encodePassword($user, $form['hash']->getData()));

            /** On rend le token inutilisable */
            $user->setForgotPasswordToken(null);

            $this->entityManager->flush();

            $this->removeCredentialsFromSession();

            $this->addFlash('success', 'Votre mot de passe a été modifié, vous pouvez dès à présent vous connecter !');

            return $this->redirectToRoute('account_login');
        }
        return $this->render('forgot_password/forgot_password_step_2.html.twig', [
            'forgotPasswordFormStep2' => $form->createView()
        ]);
    }
    private function getCredentialsFromSession(): array
    {
        return [
            'token' => $this->session->get('Reset-Password-Token-URL'),
            'userEmail' =>$this->session->get('Reset-Password-User-Email')
        ];
    }
    private function removeCredentialsFromSession(): void
    {
        $this->session->remove('Reset-Password-Token-URL');

        $this->session->remove('Reset-Password-User-Email');
    }


}