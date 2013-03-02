<?php

namespace PvtTest\Security;

use Pvt\Core\AccessToken;
use Pvt\Security\OAuth2Token;

class OAuth2TokenTest extends \PvtTest\PvtTestCase
{
    private $token;

    public function setup()
    {
        $this->token = new OAuth2Token(new AccessToken(10001, 'abcdefgh'));
    }

    public function testTokenNeverExpires()
    {
        $this->assertFalse($this->token->hasExpired());
        $this->assertEquals(PHP_INT_MAX, $this->token->getExpiresIn());
    }

    public function testClientIsAlwaysAndroidApp()
    {
        $this->assertEquals('android', $this->token->getClientId());
    }

    public function testScopeIsNull()
    {
        $this->assertNull($this->token->getScope());
    }

    public function testDataIsUserId()
    {
        $this->assertEquals(10001, $this->token->getData());
    }

    public function testTokenIsFromParentToken()
    {
        $this->assertEquals('abcdefgh', $this->token->getToken());
    }
}
