<?php

namespace Guzzle\Http\Middleware\Tests;

use Guzzle\Http\Middleware\EscherCredential;
use Guzzle\Http\Middleware\EscherMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

trait TestHelper
{
    private $clientHistory = [];

    private function createTestClientWithEscherMiddlewareStack(): Client
    {
        $stack = $this->createTestStackWithMiddleware();

        $history = Middleware::history($this->clientHistory);
        $stack->push($history);

        return new Client(['handler' => $stack]);
    }

    private function createTestStackWithMiddleware(): HandlerStack
    {
        $stack = HandlerStack::create();
        $middleware = $this->createTestMiddleware();
        $stack->push($middleware);

        return $stack;
    }

    private function createTestMiddleware(): EscherMiddleware
    {
        return new EscherMiddleware($this->createTestCredential());
    }

    private function createTestCredential(): EscherCredential
    {
        return new EscherCredential('test_key', 'test_secret', 'test/credential/scope');
    }

    private function getRequestFromClientHistory($requestNumber = 0): ?RequestInterface
    {
        return $this->clientHistory[$requestNumber]['request'] ?? null;
    }

}
