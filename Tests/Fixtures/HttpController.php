<?php

namespace Shaygan\AffiliateBundle\Tests\Fixtures;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class HttpController
{
    public function visit(): Response
    {
        return new Response('Visit recorded');
    }

    public function tracking(Request $request): JsonResponse
    {
        return new JsonResponse($request->getSession()->all());
    }
}
