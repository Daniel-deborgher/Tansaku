<?php

namespace App\Controller;

use App\Entity\PasswordUpdate;
use App\Entity\User;
use App\Form\AccountType;
use App\Form\PasswordUpdateType;
use App\Form\RegistrationType;
use App\Service\SendEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface as Password;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class AccountController extends AbstractController
{
    /**
     * Permet d'afficher et de gérer le formulaire de connexion
     * 
     * @Route("/login", name="account_login")
     * 
     * @return Response
     */
    public function login(AuthenticationUtils $utils)
    {
        $error = $utils->getLastAuthenticationError();
        $username = $utils->getLastUsername();
        return $this->render('account/login.html.twig', [
            'hasError' => $error !== null,
            'username' => $username
        ]);
    }
    /**
     * Permet de se déconnecter
     *
     * @Route ("/logout", name="account_logout")
     * @return void
     */
    public function logout(){
        //rien
    }
    /**
     * permet d'afficher le formulaire d'inscription
     *
     * @Route ("/inscription", name="account_register")
     * @return Response
     */
    public function register(Request $request, Password $encoder, TokenGeneratorInterface $tokenGenerator, SendEmail $sendMail){
        $entityManager = $this->getDoctrine()->getManager();
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);


        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $fichier = $form->get("picture")->getData();
            if($fichier){
                $nomFichier = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                $nomFichier .= uniqid();
                $nomFichier .= "." . $fichier->guessExtension();
                $nomFichier = str_replace(" ", "_", $nomFichier);
                //on enregistre le fichier téléchargé dans un dossier public/images
                $dossier = $this->getParameter("dossier_images");
                $fichier->move($dossier, $nomFichier);
                $user->setPicture($nomFichier);
            }
          $registrationToken = $tokenGenerator->generateToken();

          $user->setRegistrationToken($registrationToken);

          $hash = $encoder->encodePassword($user, $user->getHash());
          $user->setHash($hash);

          $entityManager->persist($user);
          $entityManager->flush(); 
          
          $sendMail->send([
              'recipient_email' => $user->getEmail(),
              'subject'         => "Vérification de votre adresse e-mail pour activer votre compte",
              'html_template'   => "account/registrer_confirmation_email.html.twig",
              'context'         => [
                  'userID' => $user->getId(),
                  'registrationToken' => $registrationToken

              ]
          ]);

          $this->addFlash("success", "Votre compte a bien été créé, vérifiez vos e-mails pour l'activer !");

          return $this->redirectToRoute("account_login");
        }
        

        return $this->render('account/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }
    /**
     * @Route ("/{id<\d+>}/{token}", name="app_verify_account", methods={"GET"})
     * @return Response
     */
        public function verifyUserAccount(User $user, $token){
            $entityManager = $this->getDoctrine()->getManager();

            if(($user->getRegistrationToken() === null) || ($user->getRegistrationToken()!== $token)){
                throw new AccessDeniedException();
            }
            $user->setIsVerified(true);
            $user->setRegistrationToken(null);
            $entityManager->flush();

            $this->addFlash(
                'success', 'Votre compte est à présent vérifié ! Vous pouvez vous connecter !'
            );
            return $this->redirectToRoute('account_login');
        }

    /**
     * Permet d'afficher et de de traiter le formulaire de modification du profil
     * 
     * @Route("/mon-profil/modification", name="account_profile")
     * @IsGranted("ROLE_USER")
     * 
     * @return Response
     */

    public function profile(Request $request){
    $entityManager = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $form =$this->createForm(AccountType::class, $user);
    
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash(
                'success', 'Les données du profil ont été ajoutées avec succès !'
            );
        }

        return $this->render('account/profile.html.twig',
    ['form' => $form->createView()]);
    }

    /**
     * Permet de modifier le mot de passe 
     * 
     * @Route("/mon-profile/modification-mot-de-passe", name="account_updatePassword")
     * @IsGranted("ROLE_USER")
     *
     * @return Response
     */
    public function updatePassword(Request $request, Password $encoder){
        $entityManager = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        $passwordUpdate = new PasswordUpdate(); 

        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
             // Vérifier que le oldPassword du formulaire soit le même que le password de l'user
             
             if (!password_verify($passwordUpdate->getOldPassword(), $user->getHash())) {
                $form->get('oldPassword')->addError(new FormError("Le mot de passe que vous avez saisi ne correspond pas à votre mot de passe actuel"));
             }
             else {
                $newPassword = $passwordUpdate->getNewPassword();
                $hash = $encoder->encodePassword($user, $newPassword);

                $user->setHash($hash);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash(
                    'success',
                    "Votre mot de passe a bien été modifié !"
                );
                return $this->redirectToRoute('home');
             }
        }

        return $this->render('account/password.html.twig',
    ['form' => $form->createView()]);
    }
    
}
