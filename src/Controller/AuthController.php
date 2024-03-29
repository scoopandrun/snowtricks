<?php

namespace App\Controller;

use App\Core\FlashClasses;
use App\DTO\UserInformationDTO;
use App\Entity\User;
use App\Form\PasswordResetStep1Type;
use App\Form\PasswordResetStep2Type;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\AppAuthenticator;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthController extends AbstractController
{
    #[Route(
        path: '/signup',
        name: 'auth.signup',
        methods: ['GET', 'POST']
    )]
    public function signup(
        Request $request,
        Security $security,
        UserService $userService,
        EntityManagerInterface $entityManager,
    ): Response {
        $userInformation = new UserInformationDTO();
        $form = $this->createForm(RegistrationFormType::class, $userInformation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = new User();

            $userService->fillInUserEntityFromUserInformationDTO($userInformation, $user);

            $userService->sendVerificationEmail($user);

            // Flush after setting the verification token
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash(FlashClasses::SUCCESS, "Your account has been created.");
            $this->addFlash(FlashClasses::WARNING, "You must validate your account with the link sent by email before editing tricks or posting comments.");

            $redirectResponse = $security->login($user, AppAuthenticator::class);

            return $redirectResponse ?? $this->redirectToRoute('homepage.index');
        }

        return $this->render('auth/signup.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route(
        path: '/login',
        name: 'auth.login',
        methods: ['GET', 'POST'],
    )]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('homepage.index');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'auth/login.html.twig',
            [
                'last_username' => $lastUsername,
                'error' => $error,
            ]
        );
    }

    #[Route(
        path: '/logout',
        name: 'auth.logout',
        methods: ['GET'],
    )]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(
        path: '/verify-email/{token}',
        name: 'auth.verify-email',
        methods: ['GET'],
        requirements: ['token' => Requirement::CATCH_ALL],
    )]
    public function verifyUserEmail(
        string $token,
        UserRepository $userRepository,
        UserService $userService,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $userRepository->findOneBy(['emailVerificationToken' => $token]);

        if (is_null($user)) {
            $this->addFlash(FlashClasses::DANGER, "This validation link is not valid.");
            return $this->redirectToRoute('homepage.index');
        }

        $userService->verifyEmail($user);

        $entityManager->flush();

        $this->addFlash(FlashClasses::SUCCESS, 'Your email address has been verified.');

        return $this->redirectToRoute('homepage.index');
    }

    #[Route(
        path: '/password-reset',
        name: 'auth.password-reset-step-1',
        methods: ['GET', 'POST'],
    )]
    public function step1email(
        UserService $userService,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        Request $request,
    ): Response {
        $userInformation = new UserInformationDTO();
        $form = $this->createForm(PasswordResetStep1Type::class, $userInformation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $userInformation->email;

            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user) {
                $emailSent = $userService->sendPasswordResetEmail($user);
            }

            if (!$user || $emailSent) {
                $entityManager->flush();

                $message = "If this email is linked to an account, you will receive
                    an email message with a reset link.";

                $this->addFlash(FlashClasses::SUCCESS, $message);
            } else {
                $message = "An issue occurred when sending the email.";

                $this->addFlash(FlashClasses::DANGER, $message);
            }

            return $this->redirectToRoute('auth.password-reset-step-1');
        }

        return $this->render('auth/password-reset-step-1.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(
        '/password-reset/{token}',
        name: 'auth.password-reset-step-2',
        methods: ['GET', 'POST'],
        requirements: ['token' => Requirement::CATCH_ALL],
    )]
    public function step2password(
        string $token,
        UserRepository $userRepository,
        UserService $userService,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $userRepository->findOneBy(['passwordResetToken' => $token]);

        if (is_null($user)) {
            $this->addFlash(FlashClasses::DANGER, "This password reset link is not valid.");
            return $this->redirectToRoute('homepage.index');
        }

        $userInformation = new UserInformationDTO();
        $form = $this->createForm(PasswordResetStep2Type::class, $userInformation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userService->resetPassword($user, $userInformation);

            $entityManager->flush();

            $this->addFlash(FlashClasses::SUCCESS, 'Your password has been reset.');

            return $this->redirectToRoute('homepage.index');
        }

        return $this->render('auth/password-reset-step-2.html.twig', [
            'form' => $form,
        ]);
    }
}
