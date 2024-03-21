<?php

namespace App\Service;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class UserService
{
    public function __construct(
        private TokenGeneratorInterface $tokenGenerator,
        private MailerInterface $mailer,
        private LoggerInterface $logger,
    ) {
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
            ->htmlTemplate("email/accountVerification.html.twig")
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
}
