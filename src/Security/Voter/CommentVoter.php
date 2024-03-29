<?php

namespace App\Security\Voter;

use App\Entity\Comment;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;


class CommentVoter extends Voter
{
    public const VIEW = 'comment_view';
    public const CREATE = 'comment_create';
    public const EDIT = 'comment_edit';
    public const DELETE = 'comment_delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return
            in_array($attribute, [self::VIEW, self::CREATE]) ||
            (in_array($attribute, [self::EDIT, self::DELETE]) && $subject instanceof Comment);
    }

    /**
     * @param ?Comment $subject 
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        // Everyone can view comments
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
                return $user->isVerified();
                break;

            case self::EDIT:
            case self::DELETE:
                // Users can only edit or delete their own comments
                return $user->isVerified() && $user === $subject->getAuthor();
                break;
        }

        return false;
    }
}
