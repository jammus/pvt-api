<?php

namespace PvtTest\Security;

use Pvt\Core\Password;
use Pvt\Core\User;
use Pvt\Security\OAuth2Client;

class OAuth2TokenStorageCredentialsTest extends OAuth2TokenStorageTestCase
{
    public function testImplementsIOAuth2GrantUser()
    {
        $this->assertInstanceOf('\OAuth2\IOAuth2GrantUser', $this->storage);
    }

    public function testLooksUpUserByEmailAddress()
    {
        $this->userStore->expects($this->once())
            ->method('fetchByEmail')
            ->with('user@example.com');

        $this->storage->checkUserCredentials(new OAuth2Client(), 'user@example.com', 'hunter2');
    }

    public function testReturnsFalseWhenUserNotFound()
    {
        $this->assertFalse($this->storage->checkUserCredentials(new OAuth2Client(), 'user@example.com', 'hunter2'));
    }

    public function testReturnsTrueWhenPasswordsMatch()
    {
        $this->whenUsersPasswordIs('hunter2');
        $this->assertTrue($this->storage->checkUserCredentials(new OAuth2Client(), 'user@example.com', 'hunter2') !== false);
    }

    public function testReturnsUserIdInDataWhenPasswordsMatch()
    {
        $this->whenUsersPasswordIs('hunter2');
        $result = $this->storage->checkUserCredentials(new OAuth2Client(), 'user@example.com', 'hunter2');
        $this->assertEquals(10001, $result['data']);
    }

    public function testReturnsFalseWhenPasswordsDoNotMatch()
    {
        $this->whenUsersPasswordIs('hunter2');
        $this->assertFalse($this->storage->checkUserCredentials(new OAuth2Client(), 'user@example.com', 'password'));
    }

    private function whenUsersPasswordIs($password)
    {
        $user = new User(10001, '', '', Password::fromPlainText($password));

        $this->userStore->expects($this->any())
            ->method('fetchByEmail')
            ->will($this->returnValue($user));
    }
}
