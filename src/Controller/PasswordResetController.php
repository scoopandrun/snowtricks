<?php

namespace App\Controller;

use App\Core\FlashClasses;
use App\DTO\UserInformation;
use App\Form\PasswordResetStep1Type;
use App\Form\PasswordResetStep2Type;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

class PasswordResetController extends AbstractController
{
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
        $userInformation = new UserInformation();
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

        $userInformation = new UserInformation();
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
