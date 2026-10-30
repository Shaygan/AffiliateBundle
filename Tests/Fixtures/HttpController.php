<?php

namespace Shaygan\AffiliateBundle\Tests\Fixtures;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class HttpController
{
    public function __construct(private EventDispatcherInterface $dispatcher) {}

    public function register(Request $request): Response
    {
        $response = new JsonResponse();
        $this->dispatcher->dispatch(
            new RegistrationEvent(new User($request->query->getInt('user', 77)), $response),
            \App\AppEvents::USER_REGISTRATION_COMPLETED
        );
        // Observe the event boundary before the later kernel.response listeners run.
        $response->setData(['tracking_after_registration' => $request->getSession()->all()]);
        return $response;
    }

    public function visit(): Response
    {
        return new Response('Visit recorded');
    }

    public function tracking(Request $request): JsonResponse
    {
        return new JsonResponse($request->getSession()->all());
    }
}
