<?php

namespace Shaygan\AffiliateBundle\Tests\Fixtures;

use Symfony\Component\HttpFoundation\Response;

final class RegistrationEvent
{
    public function __construct(private User $user, private Response $response) {}
    public function getUser(): User { return $this->user; }
    public function getResponse(): Response { return $this->response; }
}
