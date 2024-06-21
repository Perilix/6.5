<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->isBanned()) {
            throw new CustomUserMessageAuthenticationException('Votre compte a été banni. Pour toutes réclamations, contactez le support contact@biture-numerique.fr');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Aucun contrôle supplémentaire après l'authentification
    }
}