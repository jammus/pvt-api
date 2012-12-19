<?php

namespace PvtTest\Core;

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
}
