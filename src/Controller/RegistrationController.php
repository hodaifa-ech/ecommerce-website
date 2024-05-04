<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\AppCustomAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppCustomAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )

            );


            $user->setIsVerified(0);
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
// private $entityManager;
// private $twilioService;
// public function __construct(EntityManagerInterface $entityManager, TwilioService $twilioService)
// {
//     $this->entityManager = $entityManager;
//     $this->twilioService = $twilioService;
// }
// #[Route('/register', name: 'app_register')]
// public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppCustomAuthenticator $authenticator, EntityManagerInterface $entityManager, SessionInterface $session): Response
// {
//     $user = new User();
//     $form = $this->createForm(RegistrationFormType::class, $user);
//     $form->handleRequest($request);

//     if ($form->isSubmitted() && $form->isValid()) {
//         // encode the plain password
//         $user->setPassword(
//             $userPasswordHasher->hashPassword(
//                 $user,
//                 $form->get('plainPassword')->getData()
//             )
//         );

//         $otp = sprintf('%06d', mt_rand(0, 999999));
//         $this->twilioService->sendWhatsappOTP($form->get('tele')->getData(), $otp);

//         $session->set('username', $form->get('username')->getData());
//         $session->set('otp', $otp);
//         $session->set('tele', $form->get('tele')->getData());


//         $user->setIsVerified(0);
//         $entityManager->persist($user);
//         $entityManager->flush();

//         // Authenticate the user
//         $authenticated = $userAuthenticator->authenticateUser(
//             $user,
//             $authenticator,
//             $request
//         );

//         // Check if the user was successfully authenticated
//         if ($authenticated instanceof Response) {
//             // User authenticated successfully, redirect to 'verify' route
//             return $this->redirectToRoute('verify');
//         }

//         // If user authentication fails, handle accordingly
//         // You can return an error response or redirect to the login page
//         // For example:
//         return $this->redirectToRoute('app_login');
//     }

//     // If form submission is not valid, render the registration form
//     return $this->render('registration/register.html.twig', [
//         'registrationForm' => $form->createView(),
//     ]);
// }




// #[Route('/verify', name: 'verify')]
// public function verify(Request $request, SessionInterface $session): Response
// {
//     $otp = $session->get('tele');
//     var_dump($otp);
//     $msg = "";
//     if ($session->get('otp') !== null && $session->get('username') !== null) {
//         $otpFromForm = $request->request->get('otp');
//         $sessionOtp = $session->get('otp');
//         if (empty($otpFromForm)) {
//             $msg = "";
//         } else {
//             if ($otpFromForm == $sessionOtp) {
//                 $sessionUsername = $session->get('username');
//                 $userRepository = $this->entityManager->getRepository(User::class);
//                 $user = $userRepository->findOneBy(['username' => $sessionUsername]);
//                 if ($user) {
//                     $user->setIsVerified(1);
//                     $this->entityManager->persist($user);
//                     $this->entityManager->flush();
//                     $msg = 'Account verified successfully.';
//                     //  return $this->redirectToRoute('dashboard');
//                 } else {
//                     return $this->redirectToRoute('app_login');
//                 }
//             } else {
//                 $msg = 'Verification code is incorrect.';
//             }
//         }
//     } else {
//         return $this->redirectToRoute('app_login');
//     }
//     return $this->render('registration/verify.html.twig', ['message' => $msg]);
// }
// }