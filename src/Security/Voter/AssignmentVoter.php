<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Assignment;
use App\Entity\User;
use App\Repository\RestaurantRepository;
use Symfony\Bundle\SecurityBundle\Security;

final class AssignmentVoter extends Voter
{
    public const EDIT = 'ASSIGNMENT_EDIT';
    public const DELETE = 'ASSIGNMENT_EDIT';
    public const VIEW = 'ASSIGNMENT_VIEW';

    public function __construct(
        private readonly Security $security,
        private readonly RestaurantRepository $restaurantRepository
    ) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Assignment;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        $success = false;
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            $vote?->addReason('The user must be logged in to access this resource.');
            return $success;
        }
        if ($this->security->isGranted(User::ROLE_ADMIN) === true) {
            $success = true;
        } elseif ($this->security->isGranted(User::ROLE_RESTAURANT_OWNER) === true) {
            $ids = $this->restaurantRepository
            ->createBuilderForUser($user)->select('r.id')->getQuery()->getSingleColumnResult();
             // ... (check conditions and return true to grant permission) ...
            switch ($attribute) {
                case self::EDIT:
                    // logic to determine if the user can EDIT
                    // return true or false
                    $success = in_array($subject->getRestaurant()->getId(), $ids);
                    break;

                case self::VIEW:
                    // logic to determine if the user can VIEW
                    // return true or false
                    $success = in_array($subject->getRestaurant()->getId(), $ids);
                    break;
            }
        }
        return $success;
    }
}
