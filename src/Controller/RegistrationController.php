<?php

namespace App\Controller;

use App\Core\FlashClasses;
use App\DTO\UserInformation;
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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

class RegistrationController extends AbstractController
{
    public function __construct()
    {
    }

    #[Route(
        path: '/signup',
        name: 'auth.signup',
        methods: ['GET', 'POST']
    )]
    public function register(
        Request $request,
        Security $security,
        UserService $userService,
        EntityManagerInterface $entityManager,
    ): Response {
        $userInformation = new UserInformation();
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

            $security->login($user, AppAuthenticator::class);

            return $this->redirectToRoute('homepage.index');
        }

        return $this->render('auth/signup.html.twig', [
            'registrationForm' => $form,
        ]);
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
}
