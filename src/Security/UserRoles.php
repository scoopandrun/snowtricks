<?php

namespace App\Security;

class UserRoles
{
    public const USER = 'ROLE_USER';
    public const VERIFIED = 'ROLE_VERIFIED';

    public function getUser(): string
    {
        return static::USER;
    }

    public function getVerified(): string
    {
        return static::VERIFIED;
    }
}
