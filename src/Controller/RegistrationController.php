<?php

namespace App\Controller;

use App\Core\FlashClasses;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\AppAuthenticator;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    public function __construct(
        private UserService $userService,
    ) {
    }

    #[Route(
        '/signup',
        name: 'auth.signup',
        methods: ['GET', 'POST']
    )]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security,
        EntityManagerInterface $entityManager,
    ): Response {
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

            $this->userService->sendVerificationEmail($user);

            // Flush after setting the verification token
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash(FlashClasses::SUCCESS, "Your account has been created.");
            $this->addFlash(FlashClasses::WARNING, "You must validate your account with the link sent by email before editing tricks or posting comments.");

            $security->login($user, AppAuthenticator::class);

            return $this->redirectToRoute('homepage.index');
        }

        return $this->render('auth/signup.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verifyEmail/{token}', name: 'auth.verify-email')]
    public function verifyUserEmail(
        string $token,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $userRepository->findOneBy(['emailVerificationToken' => $token]);

        if (is_null($user)) {
            $this->addFlash(FlashClasses::DANGER, "This validation link is not valid.");
            return $this->redirectToRoute('homepage.index');
        }

        $this->userService->verifyEmail($user);

        $entityManager->flush();

        $this->addFlash(FlashClasses::SUCCESS, 'Your email address has been verified.');

        return $this->redirectToRoute('homepage.index');
    }
}
