<?php

namespace App\Service;

use App\DTO\UserInformation;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class UserService
{
    public function __construct(
        private TokenGeneratorInterface $tokenGenerator,
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public function makeUserInformationDTOFromEntity(User $user): UserInformation
    {
        return new UserInformation(
            $user->getUsername(),
            $user->getEmail(),
        );
    }

    public function fillInUserEntityFromUserInformationDTO(UserInformation $userInformation, User $user): void
    {
        $user
            ->setUsername($userInformation->username)
            ->setEmail($userInformation->email);

        if ($userInformation->newPassword) {
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $userInformation->newPassword
                )
            );
        }
    }

    /**
     * Send an e-mail to verify the user's e-mail address.
     * 
     * @param string $email E-mail address to be verified.
     * 
     * @return bool `true` on success, `false` on failure.
     */
    public function sendVerificationEmail(User $user): bool
    {
        $token = $this->tokenGenerator->generateToken();

        $verificationURL = "http://snowtricks.localhost/verifyEmail/$token";

        $user->setEmailVerificationToken($token);

        $email = (new TemplatedEmail())
            ->from(new Address("no-reply@snowtricks.localhost", "Snowtricks"))
            ->to($user->getEmail())
            ->subject("Validate your email address")
            ->htmlTemplate("email/account-verification.html.twig")
            ->context([
                "username" => $user->getUsername(),
                "verificationURL" => $verificationURL,
            ]);

        try {
            $this->mailer->send($email);

            return true;
        } catch (TransportExceptionInterface $e) {
            $this->logger->error($e);
            return false;
        }
    }

    /**
     * Set an email as verified.
     * 
     * @param string $token 
     * 
     * @return void
     */
    public function verifyEmail(User $user): void
    {
        $user->setIsVerified(true);
        $user->setEmailVerificationToken(null);
    }

    /**
     * @return bool `true` on success, `false` on failure.
     */
    public function sendPasswordResetEmail(User $user): bool
    {
        $token = $this->tokenGenerator->generateToken();

        $passwordResetURL = "http://snowtricks.localhost/password-reset/$token";

        $user->setPasswordResetToken($token);

        $email = (new TemplatedEmail())
            ->from(new Address("no-reply@snowtricks.localhost", "Snowtricks"))
            ->to($user->getEmail())
            ->subject("Reset your password")
            ->htmlTemplate("email/password-reset.html.twig")
            ->context([
                "username" => $user->getUsername(),
                "passwordResetURL" => $passwordResetURL,
            ]);

        try {
            $this->mailer->send($email);

            return true;
        } catch (TransportExceptionInterface $e) {
            $this->logger->error($e);
            return false;
        }
    }

    public function resetPassword(User $user, UserInformation $userInformation): void
    {
        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                $userInformation->newPassword
            )
        );
    }
}
