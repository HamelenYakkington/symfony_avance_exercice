<?php

namespace App\Security\Voter;

use App\Entity\Task;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;


final class TaskVoter extends Voter
{
    public const EDIT   = 'POST_EDIT';
    public const VIEW   = 'POST_VIEW';
    public const DELETE = 'POST_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof \App\Entity\Task;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($subject, $user);

            case self::VIEW:
                return $this->canView($subject, $user);

            case self::DELETE:
                return $this->canDelete($user);

            default:
                return false;
        }
    }

    protected function canEdit(Task $task, UserInterface $user) : bool {
        return $this->isAdmin($user) ? true : $this->isAuthor($task, $user);
    }

    protected function canView(Task $task, UserInterface $user) : bool {
        return $this->isAdmin($user) ? true : $this->isAuthor($task, $user);
    }

    protected function canDelete(UserInterface $user) : bool {
        return $this->isAdmin($user);
    }

    protected function isAuthor(Task $task, UserInterface $user) : bool {
        $author = $task->getAuthor();
        return $author == null ? false : $user->getUserIdentifier() == $author->getEmail();
    }

    protected function isAdmin(UserInterface $user) : bool {
        return in_array("ROLE_ADMIN", $user->getRoles(), true);
    }
}
