<?php

namespace App\Security;

use App\Entity\Usuario;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof Usuario) {
            return;
        }

        if ($user->isBlocked()) {
            // the message passed to this exception is meant to be displayed to the user
            throw new CustomUserMessageAccountStatusException('Your user account is blocked.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof Usuario) {
            return;
        }
    }
}
