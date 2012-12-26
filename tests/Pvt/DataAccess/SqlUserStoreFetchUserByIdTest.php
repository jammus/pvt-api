<?php

namespace PvtTest\DataAccess;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;

use Pvt\Core\User;
use Pvt\DataAccess\SqlUserStore;

class SqlUserStoreFetchUserByIdTest extends \PvtTest\PvtTestCase
{
    private $db;

    private $store;

    public function setup()
    {
        $this->db = $this->getMockBuilder('\Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->store = new SqlUserStore($this->db);
    }

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
                    'password' => 'password'
                )
            ));
        $expectedUser = new User(
            1234,
            'Test User', 
            'test@example.com'
        );
        $this->assertEquals($expectedUser, $this->store->fetchById(1234));
    }
}
