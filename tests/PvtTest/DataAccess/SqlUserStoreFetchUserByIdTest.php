<?php

namespace PvtTest\DataAccess;

use Doctrine\DBAL\DBALException;

use Pvt\Core\Password;
use Pvt\Core\User;

class SqlUserStoreFetchUserByIdTest extends SqlUserStoreTestCase
{
    public function testFetchesAssociativeArray()
    {
        $this->db->expects($this->once())
            ->method('fetchAssoc')
            ->with(
                'SELECT * FROM users WHERE id = :id',
                array('id' => 1234)
            );
        $this->store->fetchById(1234);
    }

    public function testReturnsUserObject()
    {
        $this->db->expects($this->once())
            ->method('fetchAssoc')
            ->will($this->returnValue(
                array(
                    'id' => 1234,
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'password' => 'passwordhash'
                )
            ));
        $expectedUser = new User(
            1234,
            'Test User', 
            'test@example.com',
            Password::fromHash('passwordhash')
        );
        $this->assertEquals($expectedUser, $this->store->fetchById(1234));
    }

    public function testReturnsNullWhenNoRowFound()
    {
        $result = $this->store->fetchById(987);
        $this->assertNull($result);
    }
}
