<?php

namespace Shaygan\AffiliateBundle\Tests\Integration;

abstract class HttpTestCase extends DatabaseTestCase
{
    private $server;
    private string $address;
    private string $serverLog;
    protected array $cookies = [];

    protected function setUp(): void
    {
        parent::setUp();
        $socket = stream_socket_server('tcp://127.0.0.1:0', $error, $message);
        if (!$socket) {
            throw new \RuntimeException($message, $error);
        }
        $this->address = stream_socket_get_name($socket, false);
        fclose($socket);
        $this->serverLog = tempnam(sys_get_temp_dir(), 'affiliate-http-');
        $environment = getenv();
        $environment['DATABASE_URL'] = getenv('DATABASE_URL') ?: 'sqlite:///'.$this->em->getConnection()->getParams()['path'];
        $this->server = proc_open(
            [PHP_BINARY, '-S', $this->address, dirname(__DIR__).'/Fixtures/http.php'],
            [0 => ['pipe', 'r'], 1 => ['file', $this->serverLog, 'a'], 2 => ['file', $this->serverLog, 'a']],
            $pipes,
            dirname(__DIR__, 2),
            $environment
        );
        if (!is_resource($this->server)) {
            throw new \RuntimeException('Unable to start the HTTP fixture');
        }
        fclose($pipes[0]);
        for ($attempt = 0; $attempt < 100; ++$attempt) {
            $connection = @stream_socket_client('tcp://'.$this->address, $error, $message, 0.05);
            if ($connection) {
                fclose($connection);
                return;
            }
            usleep(50000);
        }
        throw new \RuntimeException('HTTP fixture did not start: '.file_get_contents($this->serverLog));
    }

    protected function tearDown(): void
    {
        if (is_resource($this->server)) {
            proc_terminate($this->server);
            proc_close($this->server);
        }
        if (isset($this->serverLog)) {
            unlink($this->serverLog);
        }
        parent::tearDown();
    }

    protected function request(string $path, ?string $referer = null): array
    {
        $headers = [];
        if ($this->cookies) {
            $headers[] = 'Cookie: '.http_build_query($this->cookies, '', '; ');
        }
        if ($referer !== null) {
            $headers[] = 'Referer: '.$referer;
        }
        $context = stream_context_create(['http' => ['header' => implode("\r\n", $headers), 'ignore_errors' => true, 'timeout' => 20]]);
        $body = file_get_contents('http://'.$this->address.$path, false, $context);
        $responseHeaders = $http_response_header;
        foreach ($responseHeaders as $header) {
            if (stripos($header, 'Set-Cookie: ') === 0) {
                $cookie = \Symfony\Component\HttpFoundation\Cookie::fromString(substr($header, 12));
                if ($cookie->getExpiresTime() !== 0 && $cookie->getExpiresTime() < time()) {
                    unset($this->cookies[$cookie->getName()]);
                } else {
                    $this->cookies[$cookie->getName()] = $cookie->getValue();
                }
            }
        }
        self::assertNotFalse($body, file_get_contents($this->serverLog));
        preg_match('/\s(\d{3})\s/', $responseHeaders[0], $status);
        self::assertSame(200, (int) $status[1], $body."\n".file_get_contents($this->serverLog));
        return ['body' => $body, 'headers' => $responseHeaders];
    }
}
