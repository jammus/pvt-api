<?php

namespace PvtTest\Interactors;

use Pvt\Core\AccessToken;
use Pvt\Core\Password;
use Pvt\Core\User;
use Pvt\Interactors\AuthenticateUserWithPassword;
use Pvt\Interactors\AuthenticateUserResult;

class AuthenticateUserWithPasswordTest extends \PvtTest\PvtTestCase
{
    private $userStore;

    private $accessTokenStore;

    private $interactor;

    public function setup()
    {
        $this->userStore = $this->getMockBuilder('\Pvt\DataAccess\UserStore')
            ->disableOriginalConstructor()
            ->getMock();
        $this->accessTokenStore = $this->getMockBuilder('\Pvt\DataAccess\AccessTokenStore')
            ->disableOriginalConstructor()
            ->getMock();
        $this->interactor = new AuthenticateUserWithPassword($this->userStore, $this->accessTokenStore);
    }

    public function testReturnsAuthenticateUserResult()
    {
        $result = $this->interactor->execute('', '');

        $this->assertTrue($result instanceof AuthenticateUserResult, 'Result not an instance of AuthenticateUserResult');
    }

    public function testLooksUpUserByEmail()
    {
        $this->userStore->expects($this->once())
            ->method('fetchByEmail')
            ->with('example@example.com');

        $this->interactor->execute('example@example.com', '');
    }

    public function testResultIncludesInvalidEmailWhenInvalidUser()
    {
        $result = $this->interactor->execute('unknown@example.com', '');

        $this->assertTrue($result->hasError(AuthenticateUserResult::INVALID_EMAIL));
    }

    public function testResultDoesNotIncludeNotFoundWhenUserExists()
    {
        $this->userStore->expects($this->any())
            ->method('fetchByEmail')
            ->will($this->returnValue(new User(0, '', '')));

        $result = $this->interactor->execute('unknown@example.com', '');

        $this->assertFalse($result->hasError(AuthenticateUserResult::INVALID_EMAIL));
    }

    public function testResultIncludesInvalidPasswordWhenPasswordDoesNotMatch()
    {
        $user = new User(0, '', '', Password::fromPlainText('hunter2'));

        $this->userStore->expects($this->any())
            ->method('fetchByEmail')
            ->will($this->returnValue($user));

        $result = $this->interactor->execute('unknown@example.com', '*******');

        $this->assertTrue($result->hasError(AuthenticateUserResult::INVALID_PASSWORD));
    }

    public function testResultDoesNotIncludeInvalidPasswordWhenPasswordsMatch()
    {
        $user = new User(0, '', '', Password::fromPlainText('hunter2'));

        $this->userStore->expects($this->any())
            ->method('fetchByEmail')
            ->will($this->returnValue($user));

        $result = $this->interactor->execute('unknown@example.com', 'hunter2');

        $this->assertFalse($result->hasError(AuthenticateUserResult::INVALID_PASSWORD));
    }

    public function testResultDoesNotIncludeUserWhenAuthenticationUnsuccessful()
    {
        $user = new User(0, '', '', Password::fromPlainText('hunter2'));

        $this->userStore->expects($this->any())
            ->method('fetchByEmail')
            ->will($this->returnValue($user));

        $result = $this->interactor->execute('unknown@example.com', 'incorrect_password');

        $this->assertNull($result->user());
    }

    public function testResultDoesNotIncludeAccessTokenWhenAuthenticationUnsuccessful()
    {
        $user = new User(0, '', '', Password::fromPlainText('hunter2'));

        $this->userStore->expects($this->any())
            ->method('fetchByEmail')
            ->will($this->returnValue($user));

        $result = $this->interactor->execute('unknown@example.com', 'incorrect_password');

        $this->assertNull($result->accessToken());
    }

    public function testResultIncludesUserWhenAuthenticationSuccessful()
    {
        $user = new User(0, '', '', Password::fromPlainText('hunter2'));

        $this->userStore->expects($this->any())
            ->method('fetchByEmail')
            ->will($this->returnValue($user));

        $result = $this->interactor->execute('unknown@example.com', 'hunter2');

        $this->assertTrue($result->isOk());
        $this->assertEquals($user, $result->user());
    }

    public function testFetchesExistingAccessTokenForUserWhenAuthenticationIsSuccessful()
    {
        $this->whenAuthenticatesAsUser(10001, 'Test User');

        $accessToken = new AccessToken(10001, 'access_token');

        $this->accessTokenStore->expects($this->once())
            ->method('fetchByUserId')
            ->with(10001)
            ->will($this->returnValue($accessToken));

        $result = $this->interactor->execute('unknown@example.com', 'hunter2');

        $this->assertEquals($accessToken, $result->accessToken());
    }

    public function testSavesNewTokenIfNoExistingToken()
    {
        $this->whenAuthenticatesAsUser(10001, 'Test User');

        $this->accessTokenStore->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf('\Pvt\Core\AccessToken'));

        $result = $this->interactor->execute('unknown@example.com', 'hunter2');

        $this->assertEquals(10001, $result->accessToken()->userId());
        $this->assertNotNull($result->accessToken()->token());
    }

    private function whenAuthenticatesAsUser($id, $name, $email = '')
    {
        $user = new User($id, $name, $email, new AlwaysValidPassword());
        $this->userStore->expects($this->any())
            ->method('fetchByEmail')
            ->will($this->returnValue($user));
    }
}

class AlwaysValidPassword extends \Pvt\Core\Password
{
    public function __construct()
    {
    }

    public function matches($password)
    {
        return true;
    }
}
