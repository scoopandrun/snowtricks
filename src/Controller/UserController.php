<?php

namespace App\Controller;

use App\Core\FlashClasses;
use App\DTO\UserInformationDTO;
use App\Form\UserAccountType;
use App\Repository\UserRepository;
use App\Security\UserRoles;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route(
        path: '/user',
        name: 'auth.user',
        methods: ['GET', 'POST'],
    )]
    #[IsGranted(UserRoles::USER)]
    public function index(
        Security $security,
        EntityManagerInterface $entityManager,
        UserService $userService,
        Request $request,
    ): Response {
        $user = $security->getUser();

        $userInformation = $userService->makeUserInformationDTOFromEntity($user);

        $form = $this->createForm(UserAccountType::class, $userInformation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userService->fillInUserEntityFromUserInformationDTO(
                $userInformation,
                $user
            );

            $profilePictureSaved = $userService->saveProfilePicture($userInformation->profilePicture, $user);

            if ($userInformation->removeProfilePicture) {
                $userService->deleteProfilePicture($user);
            }

            $entityManager->flush();

            $this->addFlash(FlashClasses::SUCCESS, "Your user information has been successfully modified.");

            if (false === $profilePictureSaved) {
                $this->addFlash(FlashClasses::WARNING, "An issue occurred when saving the profile picture.");
            }

            return $this->redirectToRoute('auth.user');
        }

        return $this->render('auth/user.html.twig', [
            'form' => $form
        ]);
    }

    #[Route(
        path: '/user',
        name: 'auth.user.delete',
        methods: ['DELETE'],
    )]
    #[IsGranted(UserRoles::USER)]
    public function delete(
        Security $security,
        EntityManagerInterface $entityManager,
        Request $request,
        TokenStorageInterface $tokenStorage,
        UserService $userService,
    ): Response {
        $user = $security->getUser();

        $userService->deleteProfilePicture($user);

        $entityManager->remove($user);
        $entityManager->flush();

        $request->getSession()->invalidate();
        $tokenStorage->setToken(null);

        $this->addFlash(FlashClasses::WARNING, "Your account has been deleted.");

        return $this->redirectToRoute('homepage.index');
    }

    #[Route(
        path: '/send-verification-email',
        name: 'auth.user.send-verification-email',
        methods: ['GET'],
    )]
    #[IsGranted(UserRoles::USER)]
    public function sendVerificationEmail(
        Security $security,
        UserService $userService,
        EntityManagerInterface $entityManager,
    ): Response {
        /** @var \App\Entity\User */
        $user = $security->getUser();

        if ($user->isVerified()) {
            return new Response("The email address is already verified", 400);
        }

        $emailSent = $userService->sendVerificationEmail($user);

        $entityManager->flush();

        if ($emailSent) {
            return new Response("The email has been sent.");
        } else {
            return new Response("A problem occured during e-mail sending.", 500);
        }
    }
}
