<?php

namespace App\DTO;

use App\Validator\Constraints as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

class NewPasswordDTO
{
    public function __construct(
        #[Assert\NotBlank(groups: ['registration', 'password_reset_step_2'])]
        #[AppAssert\PasswordRequirements([
            'groups' => ['registration', 'account_update', 'password_reset_step_2']
        ])]
        #[\SensitiveParameter]
        public ?string $password = null,
    ) {
    }
}
