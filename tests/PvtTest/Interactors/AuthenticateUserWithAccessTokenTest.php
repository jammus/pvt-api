<?php

namespace PvtTest\Interactors;

use Pvt\Core\AccessToken;
use Pvt\Core\User;
use Pvt\Interactors\AuthenticateUserWithAccessToken;
use Pvt\Interactors\AuthenticateUserResult;

class AuthenticateUserWithAccessTokenTest extends \PvtTest\PvtTestCase
{
    private $interactor;

    private $accessTokenStore;

    public function setup()
    {
        parent::setup();
        $this->accessTokenStore = $this->getMock('\Pvt\DataAccess\AccessTokenStore');
        $this->userStore = $this->getMock('\Pvt\DataAccess\UserStore');
        $this->interactor = new AuthenticateUserWithAccessToken($this->userStore, $this->accessTokenStore);
    }

    public function testReturnsAuthenticateUserResult()
    {
        $result = $this->interactor->execute('access_token');
        $this->assertTrue($result instanceof AuthenticateUserResult);
    }

    public function testResultIncludesInvalidIfNoneSupplied()
    {
        $result = $this->interactor->execute('');
        $this->assertTrue($result->hasError(AuthenticateUserResult::INVALID_ACCESS_TOKEN));
        
        $result = $this->interactor->execute('       ');
        $this->assertTrue($result->hasError(AuthenticateUserResult::INVALID_ACCESS_TOKEN));
        
        $result = $this->interactor->execute(null);
        $this->assertTrue($result->hasError(AuthenticateUserResult::INVALID_ACCESS_TOKEN));
    }

    public function testResultIncludesFalseOrExpiredDataStore()
    {
        $this->accessTokenStore->expects($this->once())
            ->method('fetchByTokenString')
            ->with('access_token')
            ->will($this->returnValue(null));

        $result = $this->interactor->execute('access_token');

        $this->assertTrue($result->hasError(AuthenticateUserResult::FALSE_OR_EXPIRED_ACCESS_TOKEN));
    }

    public function testDontLookupInvalidAccessTokens()
    {
        $this->accessTokenStore->expects($this->never())
            ->method('fetchByTokenString');

        $result = $this->interactor->execute('');
    }

    public function testLooksUpUserByAssociatedIdWhenFound()
    {
        $accessToken = new AccessToken(10001, 'access_token');

        $this->accessTokenStore->expects($this->any())
            ->method('fetchByTokenString')
            ->will($this->returnValue($accessToken));

        $this->userStore->expects($this->once())
            ->method('fetchById')
            ->with($this->equalTo(10001));

        $result = $this->interactor->execute('access_token');
    }

    public function testIncludesUserAndAccesTokenInResultWhenFound()
    {
        $accessToken = new AccessToken(10001, 'access_token');
        $user = new User(10001, 'name', 'test@example.com');

        $this->accessTokenStore->expects($this->any())
            ->method('fetchByTokenString')
            ->will($this->returnValue($accessToken));

        $this->userStore->expects($this->once())
            ->method('fetchById')
            ->will($this->returnValue($user));

        $result = $this->interactor->execute('access_token');

        $this->assertTrue($result->isOk());
        $this->assertEquals($user, $result->user(), 'Missing or unexpected user');
        $this->assertEquals($accessToken, $result->accessToken(), 'Missing or unexpected access token');
    }

    public function testDontLookUpUserWhenTokenInvalid()
    {
        $this->accessTokenStore->expects($this->once())
            ->method('fetchByTokenString')
            ->will($this->returnValue(null));

        $this->accessTokenStore->expects($this->never())
            ->method('fetchById');

        $result = $this->interactor->execute('access_token');
    }
}
