<?php

namespace PvtTest\Core;

use Pvt\Core\AccessToken;

class AccessTokenTest extends \PvtTest\PvtTestCase
{
    public function testConstructorConfiguresToken()
    {
        $token = new AccessToken(123, 'token');

        $this->assertEquals(123, $token->userId());
        $this->assertEquals('token', $token->token());
    }

    public function testForUserIdCreatesNewToken()
    {
        $token = AccessToken::forUserId(1234);

        $this->assertEquals(1234, $token->userId());
        $this->assertNotNull($token->token());
    }

    public function testCreatedTokenIsDifferentEachTime()
    {
        $tokens = array();

        $tokens[] = AccessToken::forUserId(1234)->token();
        $tokens[] = AccessToken::forUserId(1234)->token();
        $tokens[] = AccessToken::forUserId(1234)->token();
        $tokens[] = AccessToken::forUserId(1234)->token();
        $tokens[] = AccessToken::forUserId(1234)->token();

        $tokens = array_unique($tokens);

        $this->assertEquals(5, sizeof($tokens), 'Some tokens were duplicates.');
    }

    public function testDecondedCreatedTokenBeginsWithVersionNumber()
    {
        $token = base64_decode(AccessToken::forUserId(1234)->token());
        $currentVersion = '00';
        $this->assertStringStartsWith($currentVersion, $token);
    }

    public function testNextSectionIsGenerationTimeMinusEpoch()
    {
        $token = base64_decode(AccessToken::forUserId(1234)->token());
        $parts = split('\.', $token);
        $generationTime = substr($parts[0], 2);
        $this->assertLessThan(2, (time() - AccessToken::EPOCH) - (int)$generationTime);
    }
}
