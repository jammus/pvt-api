<?php

namespace PvtTest\DataAccess;

use Pvt\Core\Password;
use Pvt\Core\User;

class SqlUserStoreFetchByEmailTest extends SqlUserStoreTestCase
{
    public function testFetchesAssociativeArray()
    {
        $this->db->expects($this->once())
            ->method('fetchAssoc')
            ->with(
                'SELECT * FROM users WHERE email = :email',
                array('email' => 'user@example.com')
            );
        $this->store->fetchByEmail('user@example.com');
    }

    public function testReturnsUserObjectWhenFound()
    {
        $this->db->expects($this->any())
            ->method('fetchAssoc')
            ->will($this->returnValue(
                array(
                    'id' => 1234,
                    'name' => 'Test User',
                    'email' => 'user@example.com',
                    'password' => 'passwordhash',
                )
            ));
        $result = $this->store->fetchByEmail('user@example.com');
        $expectedUser = new User(
            1234,
            'Test User', 
            'user@example.com',
            Password::fromHash('passwordhash')
        );
        $this->assertEquals($expectedUser, $result);
    }

    public function testReturnsNullWhenNoRowFound()
    {
        $result = $this->store->fetchByEmail('user@example.com');
        $this->assertNull($result);
    }
}
