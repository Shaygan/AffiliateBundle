<?php

namespace Shaygan\AffiliateBundle\Tests\Fixtures;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class UserProvider implements UserProviderInterface
{
    public function loadUserByIdentifier(string $identifier): UserInterface { return new User((int) $identifier); }
    public function loadUserByUsername(string $username): UserInterface { return $this->loadUserByIdentifier($username); }
    public function refreshUser(UserInterface $user): UserInterface { return $user; }
    public function supportsClass(string $class): bool { return $class === User::class; }
}
