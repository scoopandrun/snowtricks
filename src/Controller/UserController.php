<?php

namespace App\Controller;

use App\Core\FlashClasses;
use App\DTO\UserInformation;
use App\Form\UserAccountType;
use App\Security\UserRoles;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route(
        '/user',
        name: 'auth.user',
        methods: ['GET', 'POST']
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

            /** @var UserInformation */
            $userInformation = $form->getData();

            $userService->fillInUserEntityFromUserInformationDTO(
                $userInformation,
                $user
            );

            $entityManager->flush();

            // // Clear password fields
            // $userInformation->currentPassword = "";
            // $userInformation->newPassword = "";

            // $form = $this->createForm(UserInformationType::class, $userInformation);

            $this->addFlash(FlashClasses::SUCCESS, "Your user information has been successfully modified.");

            return $this->redirectToRoute('auth.user');
        }

        return $this->render('auth/user.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/sendVerificationEmail')]
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
