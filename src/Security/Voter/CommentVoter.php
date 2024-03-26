<?php

namespace App\Security\Voter;

use App\Entity\Comment;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;


class CommentVoter extends Voter
{
    public const VIEW = 'COMMENT_VIEW';
    public const CREATE = 'COMMENT_CREATE';
    public const EDIT = 'COMMENT_EDIT';
    public const DELETE = 'COMMENT_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return
            in_array($attribute, [self::VIEW, self::CREATE]) ||
            (in_array($attribute, [self::VIEW, self::EDIT]) && $subject instanceof \App\Entity\Comment);
    }

    /**
     * @param ?Comment $subject 
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User */
        $user = $token->getUser();

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::VIEW:
                // everyone can view comments
                return true;
                break;

            case self::CREATE:
                return $user->isVerified();
                break;

            case self::EDIT:
            case self::DELETE:
                return $user->isVerified() && $user === $subject->getAuthor();
                break;
        }

        return false;
    }
}
