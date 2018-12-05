<?php

namespace Guzzle\Http\Middleware\Tests;

use Guzzle\Http\Middleware\EscherMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Escher\Provider;

class EscherAcceptanceTest extends TestCase
{
    /**
     * @test
     */
    public function invoke_MiddlewareSignsRequest_EscherAuthenticationIsSuccessful()
    {
        $credential = new \Guzzle\Http\Middleware\EscherCredential('key', 'secret', 'foo/bar/baz');
        $escherProvider = new Provider('foo/bar/baz', 'key', 'secret', ['key' => 'secret']);

        $stack = HandlerStack::create($this->stubAuthenticateEndPoint($escherProvider));
        $stack->push(new EscherMiddleware($credential, $escherProvider->createEscher()));
        $client = new Client(['handler' => $stack]);

        $response = $client->get('http://localhost/authenticate?foo=bar', ['auth' => 'escher']);
        $this->assertEquals(200, $response->getStatusCode());
    }

    private function stubAuthenticateEndPoint(Provider $escherProvider): callable
    {
        return function (RequestInterface $request) use ($escherProvider) {
            $server = [
                'REQUEST_METHOD' => $request->getMethod(),
                'REQUEST_URI' => (string) $request->getUri()->getPath() . '?' . $request->getUri()->getQuery(),
                'REQUEST_TIME' => time(),
                'SERVER_NAME' => 'localhost',
                'SERVER_PORT' => 80,
            ];

            foreach ($request->getHeaders() as $header => $value) {
                $server['HTTP_' . strtoupper(str_replace('-', '_', $header))] = $value[0];
            }

            try {
                $escherProvider->createEscher()->authenticate($escherProvider->getKeyDB(), $server);
                $response = new Response(200);
            } catch (Exception $exception) {
                $response = new Response(403);
            }

            return new FulfilledPromise($response);
        };
    }
}
