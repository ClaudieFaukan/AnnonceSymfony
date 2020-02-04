<?php

namespace App\Controller;

use App\Entity\User;

use App\Form\RegistrationType;
use App\Form\ResetPasswordType;
use App\Form\EmailResetType;
use App\Form\ResetType;

use Mailgun\Mailgun;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="security_registration")
     */
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $user->setAdmin(false);

        // Creation du formulaire "inscription"
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);
    
        if($form->isSubmitted() && $form->isValid()) {

            // Encodage du mot de passe pour qu'il ne soit jamais en clair
            $hash = $encoder->encodePassword($user, $user->getPassword());

            $user->setPassword($hash);

            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('security_login');
        }


        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/connexion", name="security_login")
     */
    public function login() {
        return $this->render('security/login.html.twig');
    }

    /**
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout() {}

    /**
     * @Route("/reinitialisationmdp", name="security_reset_password")
     */
    public function resetPassword(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
    	$manager = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        //dump($user->getPassword());
    	$form = $this->createForm(ResetPasswordType::class);

    	$form->handleRequest($request);
        //dump($request);
        // die();

        // Si formulaire soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            
            // Récupération de l'ancien mdp que l'utilisateur a saisi dans le formulaire
            $oldPassword = $request->request->get('reset_password')['oldPassword'];
            /*dump($oldPassword);
            dump($newPassword);
            dump($user->getPassword());
            dump($encoder->isPasswordValid($user, $oldPassword));*/

            // Si le mot de passe est conforme au mdp associé à l'user dans la bdd
            if ($encoder->isPasswordValid($user, $oldPassword)) {

                // Récupération du nouveau mot de passe souhaité par l'utilisateur
                $newPassword = $request->request->get('reset_password')['password']['first'];

                // Encodage du mot de passe
                $hash = $encoder->encodePassword($user, $newPassword);

                // Modification du mot de passe
                $user->setPassword($hash);

                $manager->persist($user);
                $manager->flush();

                $this->addFlash('notice', 'Votre mot de passe à bien été changé !');

                return $this->redirectToRoute('mon_compte');

            } else {
                $form->addError(new FormError('Ancien mot de passe incorrect'));
            }
        }
    	
    	return $this->render('security/resetPassword.html.twig', array(
    		'form' => $form->createView(),
    	));
    }

// TEST

    // public function forgotPassword(Request $request)
    // {
    //     $entityManager = $this->getDoctrine()->getManager();
    //     $form = $this->createForm(EmailResetType::class);

    //     $form->handleRequest($request);
    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $user = $entityManager->getRepository(User::class)->findOneByEmail($form->getData()['email']);
    //         if ($user !== null) {
    //             $token = uniqid();
    //             $user->setResetPassword($token);
    //             $entityManager->persist($user);
    //             $entityManager->flush();

    //             $mgClient   = new Mailgun($this->getParameter('mailgun_api_key'));
    //             $domain     = $this->getParameter('mailgun_domain');
    //             $mailFrom   = $this->getParameter('mail_mail_from');
    //             $nameFrom   = $this->getParameter('mail_name_from');
    //             $mailTo = $user->getEmail();
    //             $result = $mgClient->sendMessage($domain, array(
    //                 'from' => "$nameFrom <$mailFrom>",
    //                 'to' => "<$mailTo>",
    //                 'subject' => 'Mot de passe oublié ?',
    //                 'html' => $this->renderView('emails/reset-password.html.twig', array('token' => $token))
    //             ));

    //             return $this->render('authentication/reset-password-confirmation.html.twig');
    //         }
    //     }

    //     return $this->render('authentication/reset-password.html.twig', array(
    //         'form' => $form->createView(),
    //     ));
    // }

    // public function forgotPasswordToken(Request $request, UserPasswordEncoderInterface $encoder)
    // {
    //     $token = $request->query->get('token');
    //     if ($token !== null) {
    //         $entityManager = $this->getDoctrine()->getManager();
    //         $user = $entityManager->getRepository(User::class)->findOneByResetPassword($token);
    //         if ($user !== null) {
    //             $form = $this->createForm(ResetType::class, $user);

    //             $form->handleRequest($request);
    //             if ($form->isSubmitted() && $form->isValid()) {
    //                 $plainPassword = $form->getData()->getPlainPassword();
    //                 $encoded = $encoder->encodePassword($user, $plainPassword);
    //                 $user->setPassword($encoded);
    //                 $entityManager->persist($user);
    //                 $entityManager->flush();

    //                 //add flash

    //                 return $this->redirectToRoute('login');
    //             }

    //             return $this->render('authentication/reset-password-token.html.twig', array(
    //                 'form' => $form->createView(),
    //             ));       
    //         }
    //     }
    // }
}

