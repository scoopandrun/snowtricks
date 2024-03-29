<?php

namespace App\Security\Voter;

use App\Entity\Trick;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;


class TrickVoter extends Voter
{
    public const VIEW = 'trick_view';
    public const CREATE = 'trick_create';
    public const EDIT = 'trick_edit';
    public const DELETE = 'trick_delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return
            in_array($attribute, [self::VIEW, self::CREATE]) ||
            (in_array($attribute, [self::EDIT, self::DELETE]) && $subject instanceof Trick);
    }

    /**
     * @param ?Trick $subject 
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        // Everyone can view tricks
        if ($attribute === self::VIEW) {
            return true;
        }

        /** @var ?User */
        $user = $token->getUser();

        // Deny create/edit access to non-authenticated visitors
        if (!$user instanceof User) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::CREATE:
            case self::EDIT:
            case self::DELETE:
                return $user->isVerified();
                break;
        }

        return false;
    }
}
