<?php

namespace App\Controller;

use App\Core\FlashClasses;
use App\Form\UserType;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route('/user', name: 'auth.user')]
    #[IsGranted('ROLE_USER')]
    public function index(
        Security $security,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $security->getUser();

        $form = $this->createForm(UserType::class, $user);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash(FlashClasses::SUCCESS, "Your user information has been successfully modified.");
        }

        return $this->render('auth/user.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/sendVerificationEmail')]
    #[IsGranted('ROLE_USER')]
    public function sendVerificationEmail(
        Security $security,
        UserService $userService,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $security->getUser();

        $emailSent = $userService->sendVerificationEmail($user);

        $entityManager->flush();

        if ($emailSent) {
            return new Response("The email has been sent.");
        } else {
            return new Response("A problem occured during e-mail sending.", 500);
        }
    }
}
