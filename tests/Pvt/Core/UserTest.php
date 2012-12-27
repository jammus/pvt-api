<?php

namespace PvtTest\Core;

use Pvt\Core\Password;
use Pvt\Core\User;

class UserTest extends \PvtTest\PvtTestCase
{
    public function testConstructorConfiguresObject()
    {
        $user = new User(1234, 'name', 'name@example.com');
        $this->assertEquals(1234, $user->id());
        $this->assertEquals('name', $user->name());
        $this->assertEquals('name@example.com', $user->email());
    }

    public function testProfileUrlIsUsersPlusUserId()
    {
        $user = new User(1234, 'name', 'name@example.com');
        $this->assertEquals('/users/1234', $user->profileUrl());
    }

    public function testCheckPasswordIsFalseByDefault()
    {
        $user = new User(1234, 'name', 'name@example.com');
        $this->assertFalse($user->checkPassword('password'));
    }

    public function testCheckPasswordTrueWhenCorrect()
    {
        $user = new User(1234, 'name', 'name@example.com', Password::fromPlainText('password'));
        $this->assertTrue($user->checkPassword('password'));
    }

    public function testCheckPasswordFailsWhenDifferent()
    {
        $user = new User(1234, 'name', 'name@example.com', Password::fromPlainText('password'));
        $this->assertFalse($user->checkPassword('pissword'));
    }
}
