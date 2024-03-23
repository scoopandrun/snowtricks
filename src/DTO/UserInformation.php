<?php

namespace App\DTO;

use App\Security\PasswordPolicy;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;

class UserInformation
{
    public function __construct(
        #[Assert\NotBlank()]
        #[Assert\Type('string')]
        public ?string $username = null,

        #[Assert\NotBlank()]
        #[Assert\Email()]
        public ?string $email = null,

        #[Assert\Type('string')]
        #[Assert\When(
            expression: 'this.newPassword',
            groups: ['password_change'],
            constraints: [
                new Assert\NotBlank(message: 'You must type your current password to set a new password.'),
                new SecurityAssert\UserPassword(message: 'Your current password is incorrect.'),
            ],
        )]
        public ?string $currentPassword = null,

        #[Assert\NotBlank(groups: ['registration'])]
        #[Assert\Type('string')]
        #[Assert\Length(min: PasswordPolicy::MIN_LENGTH, max: PasswordPolicy::MAX_LENGTH)]
        #[Assert\PasswordStrength(minScore: PasswordPolicy::MIN_STRENGTH)]
        #[Assert\NotCompromisedPassword()]
        public ?string $newPassword = null,
    ) {
    }
}
