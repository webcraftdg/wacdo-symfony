<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Restaurant;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

final class RestaurantVoter extends Voter
{
    public const EDIT = 'RESTAURANT_EDIT';
    public const ARCHIVED = 'RESTAURANT_ARCHIVED';
    public const VIEW = 'RESTAURANT_VIEW';

    public function __construct(
        private readonly Security $security
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof Restaurant;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        /**@var User $user */
        $user = $token->getUser();
        $success = false;

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            $vote?->addReason('The user must be logged in to access this resource.');
            return $success;
        }
        if ($this->security->isGranted(User::ROLE_ADMIN) === true) {
            $success = true;
        } elseif($this->security->isGranted(User::ROLE_RESTAURANT_OWNER) === true ){
            if (in_array($attribute, [self::EDIT, self::VIEW]) === true) {
                $success = $user->getId() === $subject->getOwner()->getId();
            }
        }

        return $success;
    }
}
