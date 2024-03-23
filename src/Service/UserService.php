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

    // public function checkPasswordResetEmailFormData(array $formData): array
    // {
    //     $emailIsString = is_string($email ?? null);

    //     $email = $emailIsString ? $formData["email"] : "";

    //     $emailMissing = !$email;
    //     $emailInvalid = $email && !preg_match("/.*@.*\.[a-z]+/", $email);

    //     $errorMessage = $emailMissing
    //         ? "L'adresse e-mail est requise."
    //         : (
    //             $emailInvalid
    //             ? "L'adresse e-mail est invalide."
    //             : ""
    //         );

    //     $formResult = [
    //         "error" => $errorMessage,
    //     ];

    //     return $formResult;
    // }

    // /**
    //  * @return bool `true` on success, `false` on failure.
    //  */
    // public function sendPasswordResetEmail(array $formData): bool
    // {
    //     /** @var string */
    //     $email = $formData["email"];

    //     $passwordResetToken = $this->tokenGenerator->generateToken();

    //     $tokenIsSet = $this->userRepository->setPasswordResetToken($email, $passwordResetToken);

    //     // If an error uccroed during token setting, return false
    //     if ($tokenIsSet === false) {
    //         return false;
    //     }

    //     // If no error occured but the e-mail in unknown to the database
    //     // return true without sending the e-mail (=> obfuscation)
    //     if ($tokenIsSet === 0) {
    //         return true;
    //     }

    //     // If a token really has been set, send the e-mail

    //     $emailService = new EmailService();

    //     $subject = "Réinitialisation de mot de passe";

    //     $template = "password-reset";

    //     $context = compact("passwordResetToken");

    //     $emailSent = $emailService
    //         ->setFrom($_ENV["MAIL_SENDER_EMAIL"], $_ENV["MAIL_SENDER_NAME"])
    //         ->addTo($email)
    //         ->setSubject($subject)
    //         ->setTemplate($template)
    //         ->setContext($context)
    //         ->send();

    //     return $emailSent;
    // }

    // public function verifyPasswordResetToken(string $token): bool
    // {
    //     return $this->userRepository->checkIfPasswordResetTokenIsRegistered($token);
    // }


    // public function checkPasswordResetFormData(array $formData): array
    // {
    //     $newPasswordIsString = is_string($formData["new-password"] ?? null);
    //     $passwordConfirmIsString = is_string($formData["password-confirm"] ?? null);

    //     $newPassword = $newPasswordIsString ? $formData["new-password"] : "";
    //     $passwordConfirm = $passwordConfirmIsString ? $formData["password-confirm"] : "";

    //     $newPasswordMissing = !$newPassword;
    //     $newPasswordTooShort = $newPassword && mb_strlen($newPassword < Security::MINIMUM_PASSWORD_LENGTH);
    //     $passwordConfirmMissing = !$passwordConfirm;
    //     $passwordMismatch = $newPassword !== $passwordConfirm;

    //     $errorMessage = "";

    //     switch (true) {
    //         case $newPasswordMissing:
    //             $errorMessage = "Le mot de passe est obligatoire.";
    //             break;

    //         case $newPasswordTooShort:
    //             $errorMessage =
    //                 "Le mot de passe doit être supérieur à "
    //                 . Security::MINIMUM_PASSWORD_LENGTH
    //                 . " caractères.";
    //             break;

    //         case $passwordConfirmMissing:
    //             $errorMessage = "Le mot de passe doit être retapé.";
    //             break;

    //         case $passwordMismatch:
    //             $errorMessage = "Le mot de passe n'a pas été correctement retapé.";
    //             break;

    //         default:
    //             break;
    //     }

    //     $formResult = [
    //         "error" => $errorMessage,
    //     ];

    //     return $formResult;
    // }

    // public function resetPassword(string $token, string $password): bool
    // {
    //     $hashedPassword = (new User())->setPassword($password)->getPassword();

    //     return $this->userRepository->resetPassword($token, $hashedPassword);
    // }
}
