<?php

namespace App\Service;

use App\DTO\UserInformationDTO;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class UserService
{
    public function __construct(
        private TokenGeneratorInterface $tokenGenerator,
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private UserPasswordHasherInterface $userPasswordHasher,
        private RouterInterface $router,
        private ImageManager $imageManager,
        #[Autowire('%app.uploads.pictures%/users')]
        private string $profilePictureUploadDirectory,
        #[Autowire('%app.mail.sender_email%')]
        private string $senderEmail,
        #[Autowire('%app.mail.sender_name%')]
        private string $senderName,
    ) {
    }

    public function makeUserInformationDTOFromEntity(User $user): UserInformationDTO
    {
        return new UserInformationDTO(
            $user->getUsername(),
            $user->getEmail(),
        );
    }

    public function fillInUserEntityFromUserInformationDTO(UserInformationDTO $userInformation, User $user): void
    {
        $user
            ->setUsername($userInformation->username)
            ->setEmail($userInformation->email);

        if ($userInformation->getNewPassword()) {
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $userInformation->getNewPassword()
                )
            );
        }
    }

    public function saveProfilePicture(?UploadedFile $file, User $user): bool
    {
        if (is_null($file)) {
            return true;
        }

        // Remove the old profile picture, if there is one
        $this->deleteProfilePicture($user);

        $filename = $this->imageManager->saveImage(
            $file,
            $this->profilePictureUploadDirectory,
            (string) $user->getId(),
            [
                ImageManager::SIZE_THUMBNAIL,
                ImageManager::SIZE_SMALL,
            ]
        );

        return (bool) $filename;
    }

    public function deleteProfilePicture(User $user): void
    {
        $this->imageManager->deleteImage(
            $this->profilePictureUploadDirectory,
            (string) $user->getId(),
        );
    }

    public function getProfilePicturePath(
        ?User $user,
        string $size = ImageManager::SIZE_ORIGINAL
    ): ?string {
        if (is_null($user)) {
            return null;
        }

        $fullpath = $this->imageManager->getImagePath(
            $this->profilePictureUploadDirectory,
            (string) $user->getId(),
            $size
        );

        return $fullpath;
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

        $verificationURL = $this->router->generate(
            'auth.verify-email',
            ['token' => $token],
            $this->router::ABSOLUTE_URL
        );

        $user->setEmailVerificationToken($token);

        $email = (new TemplatedEmail())
            ->from(new Address($this->senderEmail, $this->senderName))
            ->to($user->getEmail())
            ->subject("Validate your email address")
            ->htmlTemplate("email/account_verification.html.twig")
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

        $passwordResetURL = $this->router->generate(
            'auth.password-reset-step-2',
            ['token' => $token],
            $this->router::ABSOLUTE_URL
        );

        $user->setPasswordResetToken($token);

        $email = (new TemplatedEmail())
            ->from(new Address($this->senderEmail, $this->senderName))
            ->to($user->getEmail())
            ->subject("Reset your password")
            ->htmlTemplate("email/password_reset.html.twig")
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

    public function resetPassword(User $user, UserInformationDTO $userInformation): void
    {
        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                $userInformation->getNewPassword()
            )
        );
    }
}
