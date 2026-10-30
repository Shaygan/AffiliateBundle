<?php

require dirname(__DIR__).'/bootstrap.php';

$kernel = new \Shaygan\AffiliateBundle\Tests\Fixtures\Kernel('http', true);
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
try {
    $response = $kernel->handle($request, \Symfony\Component\HttpKernel\HttpKernelInterface::MAIN_REQUEST, false);
} catch (\Throwable $error) {
    file_put_contents('php://stderr', (string) $error);
    throw $error;
}
$response->send();
$kernel->terminate($request, $response);
