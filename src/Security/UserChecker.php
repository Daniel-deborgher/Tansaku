<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;


class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth (UserInterface $user)
    {
        if (!$user instanceof User) {
            return;
        }
        
    }
    public function checkPostAuth(UserInterface $user)
    {
        if (!$user instanceof User) {
            return;
        }
        if (!$user->getIsVerified()) {
            throw new DisabledException("Votre compte n'est pas encore actif, veuillez vérifiez vos mail afin de l'activer");
        }
       
    }
}