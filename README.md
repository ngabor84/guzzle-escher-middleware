[ ![Codeship Status for ngabor84/guzzle-escher-middleware](https://app.codeship.com/projects/6d4dfb70-268d-0136-3230-6a55b20f6c5c/status?branch=master)](https://app.codeship.com/projects/286867)

# Guzzle Escher Middleware

This authentication middleware add Escher sign functionality to Guzzle Http Client.

## Installation
`composer require ngabor84/guzzle-escher-middleware`

## Usage
```php
<?php

$credential = new \Guzzle\Http\Middleware\EscherCredential('key', 'secret', 'some/credential/scope');
$escherMiddleware = new \Guzzle\Http\Middleware\EscherMiddleware($credential);

$stack = \GuzzleHttp\HandlerStack::create();

$stack->push($escherMiddleware);

$client   = new \GuzzleHttp\Client(['handler' => $stack]);

// Important: set the auth option to escher to activate the middleware
$response = $client->get('http://www.8points.de', ['auth' => 'escher']);
```