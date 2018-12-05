<?php

namespace Guzzle\Http\Middleware\Tests;

use Escher\Escher;
use Guzzle\Http\Middleware\EscherMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class EscherMiddlewareTest extends TestCase
{

    use TestHelper;

    /**
     * @test
     */
    public function construct_CreateNewEscherMiddleware_WhenCredentialsPassed()
    {
        $middleware = $this->createTestMiddleware();
        $this->assertInstanceOf(EscherMiddleware::class, $middleware);
    }

    /**
     * @test
     */
    public function construct_CreateNewEscherMiddleware_WithPassedEscherObject()
    {
        $credential = $this->createTestCredential();
        $escher = Escher::create($credential->getScope())->setAuthHeaderKey('my-auth-header-key');
        $client = $this->createTestClientWithCustomEscher($credential, $escher);

        $client->post('http://httpbin.org/post', [
            'auth' => 'escher',
        ]);

        $request = $this->getRequestFromClientHistory();
        $this->assertTrue($request->hasHeader('my-auth-header-key'));
    }

    /**
     * @test
     */
    public function invoke_SignTheRequest_WhenEscherMiddlewareAddedAndAuthMethodIsEscher()
    {
        $client = $this->createTestClientWithEscherMiddlewareStack();
        $client->post('http://httpbin.org/post', [
            'auth' => 'escher',
        ]);

        $request = $this->getRequestFromClientHistory();
        $this->assertTrue($request->hasHeader('x-escher-auth'));
        $this->assertTrue($request->hasHeader('x-escher-date'));
        $this->assertHasValidAuthHeader($request);
    }

    /**
     * @test
     */
    public function invoke_NotSignTheRequest_WhenEscherMiddlewareAddedButAuthMethodIsNotEscher()
    {
        $client = $this->createTestClientWithEscherMiddlewareStack();
        $client->post('http://httpbin.org/post', [
            'auth' => 'other_auth',
        ]);

        $request = $this->getRequestFromClientHistory();
        $this->assertFalse($request->hasHeader('x-escher-auth'));
        $this->assertFalse($request->hasHeader('x-escher-date'));
    }

    /**
     * @test
     */
    public function setHashAlgo_ChangeTheHashAlgorithm_WhenCalled()
    {
        $stack = HandlerStack::create();
        $middleware = $this->createTestMiddleware();
        $middleware->setHashAlgo('SHA512');
        $stack->push($middleware);

        $history = Middleware::history($this->clientHistory);
        $stack->push($history);

        $client = new Client(['handler' => $stack]);

        $client->post('http://httpbin.org/post', [
            'auth' => 'escher',
        ]);

        $request = $this->getRequestFromClientHistory();
        $this->assertTrue($request->hasHeader('x-escher-auth'));
        $this->assertTrue($request->hasHeader('x-escher-date'));
        $authHeader = $request->getHeader('x-escher-auth')[0];
        $this->assertContains('ESR-HMAC-SHA512', $authHeader);
    }

    /**
     * @test
     */
    public function setAlgoPrefix_ChangeTheAlgorithmPrefix_WhenCalled()
    {
        $stack = HandlerStack::create();
        $middleware = $this->createTestMiddleware();
        $middleware->setAlgoPrefix('EMS');
        $stack->push($middleware);

        $history = Middleware::history($this->clientHistory);
        $stack->push($history);

        $client = new Client(['handler' => $stack]);

        $client->post('http://httpbin.org/post', [
            'auth' => 'escher',
        ]);

        $request = $this->getRequestFromClientHistory();
        $this->assertTrue($request->hasHeader('x-escher-auth'));
        $this->assertTrue($request->hasHeader('x-escher-date'));
        $authHeader = $request->getHeader('x-escher-auth')[0];
        $this->assertContains('EMS-HMAC-SHA256', $authHeader);
    }

    /**
     * @test
     */
    public function setAuthHeaderKey_ChangeTheAuthHeaderKey_WhenCalled()
    {
        $stack = HandlerStack::create();
        $middleware = $this->createTestMiddleware();
        $middleware->setAuthHeaderKey('x-ems-auth');
        $stack->push($middleware);

        $history = Middleware::history($this->clientHistory);
        $stack->push($history);

        $client = new Client(['handler' => $stack]);

        $client->post('http://httpbin.org/post', [
            'auth' => 'escher',
        ]);

        $request = $this->getRequestFromClientHistory();
        $this->assertTrue($request->hasHeader('x-ems-auth'));
    }

    /**
     * @test
     */
    public function setDateHeaderKey_ChangeTheAuthHeaderKey_WhenCalled()
    {
        $stack = HandlerStack::create();
        $middleware = $this->createTestMiddleware();
        $middleware->setDateHeaderKey('x-ems-date');
        $stack->push($middleware);

        $history = Middleware::history($this->clientHistory);
        $stack->push($history);

        $client = new Client(['handler' => $stack]);

        $client->post('http://httpbin.org/post', [
            'auth' => 'escher',
        ]);

        $request = $this->getRequestFromClientHistory();
        $this->assertTrue($request->hasHeader('x-ems-date'));
    }

    private function assertHasValidAuthHeader(RequestInterface $request): void
    {
        $authHeader = $request->getHeader('x-escher-auth')[0];
        $expectedHeaderParts = ['ESR-HMAC-SHA256', 'Credential', 'SignedHeaders', 'Signature'];

        foreach ($expectedHeaderParts as $name) {
            $this->assertContains($name, $authHeader);
        }
    }

}
