<?php

namespace App\DTO;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;

class UserInformationDTO
{
    public function __construct(
        #[Assert\NotBlank(groups: ['registration', 'account_update'])]
        #[Assert\Type('string')]
        public ?string $username = null,

        #[Assert\NotBlank(groups: ['registration', 'account_update', 'password_reset_step_1'])]
        #[Assert\Email()]
        public ?string $email = null,

        #[Assert\Image(groups: ['account_update'])]
        public ?UploadedFile $profilePicture = null,

        #[Assert\Type('bool', groups: ['account_update'])]
        public bool $removeProfilePicture = false,

        #[Assert\When(
            expression: 'this.newPassword.password',
            groups: ['account_update'],
            constraints: [
                new Assert\Sequentially(
                    [
                        new Assert\Type('string'),
                        new Assert\NotBlank(message: 'You must type your current password to set a new password.'),
                        new SecurityAssert\UserPassword(message: 'Your current password is incorrect.'),
                    ],
                    groups: ['account_update'],
                ),
            ],
        )]
        #[\SensitiveParameter]
        public ?string $currentPassword = null,

        #[Assert\Valid()]
        public NewPasswordDTO $newPassword = new NewPasswordDTO(),
    ) {
    }

    public function eraseCredentials(): void
    {
        $this->currentPassword = null;
        $this->newPassword->password = null;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword->password;
    }
}
