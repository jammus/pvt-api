<?php

namespace PvtTest\Security;

use Pvt\Core\AccessToken;
use Pvt\Security\OAuth2Client;
use Pvt\Security\OAuth2TokenStorage;

class OAuth2TokenStorageTest extends \PvtTest\PvtTestCase
{
    private $accessTokenStore;

    private $storage;

    public function setup()
    {
        $this->accessTokenStore = $this->getMock('Pvt\DataAccess\AccessTokenStore');
        $this->storage = new OAuth2TokenStorage($this->accessTokenStore);
    }

    public function testAllGrantTypesAreSupportedForAllClients()
    {
        $client = $this->getMock('\OAuth2\Model\IOAuth2Client');

        $isSupported = $this->storage->checkRestrictedGrantType(
            $client,
            'any_grant_type'
        );
        
        $this->assertTrue($isSupported);
    }

    public function testGetAccessTokenProxiesOnToAccessTokenStore()
    {
        $this->accessTokenStore->expects($this->once())
            ->method('fetchByTokenString')
            ->with('token_hash');
        
        $this->storage->getAccessToken('token_hash');
    }

    public function testReturnsNullWhenNoAccessTokenFound()
    {
        $this->assertNull($this->storage->getAccessToken('token_hash'));
    }

    public function testReturnsIOAuth2AccessTokenWhenTokenFound()
    {
        $this->whenDataStoreReturns(
            new AccessToken(10001, 'abcdefgh')
        );

        $token = $this->storage->getAccessToken('abcdefgh');
        
        $this->assertInstanceOf('OAuth2\Model\IOAuth2Token', $token);
    }

    public function testIOAuth2AccessTokenIsBasedOnReturnedAccessToken()
    {
        $this->whenDataStoreReturns(
            new AccessToken(10001, 'abcdefgh')
        );

        $token = $this->storage->getAccessToken('abcdefgh');

        $this->assertEquals(10001, $token->getData());
        $this->assertEquals('abcdefgh', $token->getToken());
    }

    public function testCreateAccessTokenProxiesOnToAccessTokenStore()
    {
        $expectedToken = new AccessToken(10001, 'abcdefgh');

        $this->accessTokenStore->expects($this->once())
            ->method('save')
            ->with($expectedToken);

        $client = $this->getMock('\OAuth2\Model\IOAuth2Client');
        
        $this->storage->createAccessToken('abcdefgh', $client, 10001, PHP_INT_MAX);
    }

    public function testGetClientAlwaysReturnsAndroid()
    {
        $client = $this->storage->getClient('any_client');
        
        $this->assertInstanceOf('\OAuth2\Model\IOAuth2Client', $client);
        $this->assertEquals('android', $client->getPublicId());
    }

    public function testClientCredentialsAreNotValidByDefault()
    {
        $client = $this->getMock('\OAuth2\Model\IOAuth2Client');
        $this->assertFalse($this->storage->checkClientCredentials($client));
    }

    public function testClientCredentialsAreCorrectWhenSecretMatchesAndroidSecret()
    {
        $androidClient = new OAuth2Client();
        $this->assertTrue($this->storage->checkClientCredentials($androidClient, '8hska3hjo320iola-28ihj2/23973owld'));
    }

    public function testClientCredentialsAreIncorrectWhenSecretDoesNotMatchAndroidSecret()
    {
        $androidClient = new OAuth2Client();
        $this->assertFalse($this->storage->checkClientCredentials($androidClient, 'some bulshit'));
    }

    private function whenDataStoreReturns($accessToken)
    {
        $this->accessTokenStore->expects($this->any())
            ->method('fetchByTokenString')
            ->will($this->returnValue($accessToken));
    }
}
