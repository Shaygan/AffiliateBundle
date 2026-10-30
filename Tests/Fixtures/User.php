<?php

namespace Shaygan\AffiliateBundle\Tests\Fixtures;

use Symfony\Component\Security\Core\User\UserInterface;

final class User implements UserInterface
{
    public function __construct(private int $id = 42) {}
    public function getId(): int { return $this->id; }
    public function getUserIdentifier(): string { return (string) $this->id; }
    public function getUsername(): string { return $this->getUserIdentifier(); }
    public function getRoles(): array { return ['ROLE_USER']; }
    public function getPassword(): ?string { return null; }
    public function getSalt(): ?string { return null; }
    public function eraseCredentials(): void {}
}
