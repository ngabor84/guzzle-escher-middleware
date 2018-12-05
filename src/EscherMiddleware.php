<?php declare(strict_types=1);

namespace Guzzle\Http\Middleware;

use Escher\Escher;
use Psr\Http\Message\RequestInterface;

class EscherMiddleware
{

    /**
     * @var Escher
     */
    private $escher;

    /**
     * @var EscherCredential
     */
    private $credential;

    public function __construct(EscherCredential $credential, Escher $escher = null)
    {
        $this->credential = $credential;
        $this->escher = $escher ?: Escher::create($this->credential->getScope());
    }

    public function __invoke(callable $handler): \Closure
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            if ($options['auth'] == 'escher') {
                $request = $this->signRequest($request);
            }

            return $handler($request, $options);
        };
    }

    private function signRequest(RequestInterface $request): RequestInterface
    {
        $headers = $this->escher->signRequest(
            $this->credential->getKey(),
            $this->credential->getSecret(),
            $request->getMethod(),
            $request->getUri(),
            $request->getBody(),
            $this->getRequestHeaders($request)
        );

        return $this->setRequestHeaders($request, $headers);
    }

    private function getRequestHeaders(RequestInterface $request): array
    {
        $headers = [];

        foreach ($request->getHeaders() as $headerName => $headerValue) {
            $headers[$headerName] = !empty($headerValue) ? implode(',', $headerValue) : '';
        }

        return $headers;
    }

    private function setRequestHeaders(RequestInterface $request, array $headers): RequestInterface
    {
        foreach ($headers as $headerName => $headerValue) {
            $request = $request->withHeader($headerName, $headerValue);
        }

        return $request;
    }

    public function setHashAlgo(string $hashAlgo): void
    {
        $this->escher->setHashAlgo($hashAlgo);
    }

    public function setAlgoPrefix(string $algoPrefix): void
    {
        $this->escher->setAlgoPrefix($algoPrefix);
    }

    public function setAuthHeaderKey(string $authHeaderKey): void
    {
        $this->escher->setAuthHeaderKey($authHeaderKey);
    }

    public function setDateHeaderKey(string $dateHeaderKey): void
    {
        $this->escher->setDateHeaderKey($dateHeaderKey);
    }

}
