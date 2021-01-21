<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskVoter extends Voter
{
    /**
     * @var Security
     */
    private $security;
    /**
     * @var string|\Stringable|UserInterface
     */
    private $user;

    /**
     * TaskVoter constructor.
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [
                'TASK_EDIT',
                'TASK_DELETE'
            ])
            && $subject instanceof \App\Entity\Task;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        $this->user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$this->user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case 'TASK_EDIT':
                if ($this->isAnonymous($subject)) {
                    return $this->isAdmin();
                }
                if ($this->isAdmin() || $this->user->getId() == $subject->getUser()->getId()) {
                    return true;
                }
                break;
            case 'TASK_DELETE':
                if ($this->isAnonymous($subject)) {
                    return $this->isAdmin();
                }
                if ($this->user->getId() == $subject->getUser()->getId()) {
                    return true;
                }

                break;
        }

        return false;
    }

    private function isAdmin()
    {
        return $this->security->isGranted("ROLE_ADMIN");
    }

    private function isAnonymous($subject)
    {
        return null === $subject->getUser();
    }
}
