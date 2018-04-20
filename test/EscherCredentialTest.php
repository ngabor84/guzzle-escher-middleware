<?php

namespace Guzzle\Http\Middleware\Tests;

use PHPUnit\Framework\TestCase;
use Guzzle\Http\Middleware\EscherCredential;

class EscherCredentialTest extends TestCase
{
    use TestHelper;

    /**
     * @test
     */
    public function construct_WithCredentialArguments_CreateACredential()
    {
        $credential = $this->createTestCredential();
        $this->assertInstanceOf(EscherCredential::class, $credential);
    }

    /**
     * @test
     */
    public function setKey_SetKey_CanGetKey()
    {
        $credential = $this->createTestCredential();
        $key = 'test_key';
        $credential->setKey($key);
        $this->assertEquals($key, $credential->getKey());
    }

    /**
     * @test
     */
    public function setSecret_SetSecret_CanGetSecret()
    {
        $credential = $this->createTestCredential();
        $secret = 'test_secret';
        $credential->setScope($secret);
        $this->assertEquals($secret, $credential->getScope());
    }

    /**
     * @test
     */
    public function setScope_SetScope_CanGetScope()
    {
        $credential = $this->createTestCredential();
        $credentialScope = 'other/credential/scope';
        $credential->setScope($credentialScope);
        $this->assertEquals($credentialScope, $credential->getScope());
    }

}
