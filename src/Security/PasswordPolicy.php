<?php

namespace App\Security;

use Symfony\Component\Validator\Constraints\PasswordStrength;

class PasswordPolicy
{
    /**
     * Minimum password length.
     */
    public const MIN_LENGTH = 10;

    /**
     * Maximum password length.
     * 
     * Symfony limit for security reasons = 4096.
     */
    public const MAX_LENGTH = 4096;

    /**
     * Minimum password strength score.
     * 
     * This is a constant of \Symfony\Component\Validator\Constraints\PasswordStrength.
     * 
     * Current value = Medium (2).
     */
    public const MIN_STRENGTH = PasswordStrength::STRENGTH_MEDIUM;
}
