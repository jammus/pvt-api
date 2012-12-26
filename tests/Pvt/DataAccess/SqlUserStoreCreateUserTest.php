<?php

namespace PvtTest\DataAccess;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Pvt\DataAccess\SqlUserStore;

class SqlUserStoreCreateUserTest extends \PvtTest\PvtTestCase
{
    private $db;

    private $store;

    public function setup()
    {
        $this->db = $this->getPartialMock('\Doctrine\DBAL\Connection[insert,lastInsertId]');
        $this->store = new SqlUserStore($this->db);
    }

    public function testInsertsDetails()
    {
        $this->db->shouldReceive('insert')
            ->with(
                'users',
                array(
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'password' => '123456',
                )
            )
            ->once();
        $this->store->create('Test User', 'test@example.com', '123456');
    }

    public function testReturnsIdOnSuccess()
    {
        $this->db->shouldReceive('lastInsertId')
            ->with('users_id_seq')
            ->andReturn(1234)
            ->once();
        $id = $this->store->create('Test User', 'test@example.com', '123456');
        $this->assertEquals(1234, $id);
    }

    public function testThrowsUniqueConstraintViolationExceptionOnDuplicateEmail()
    {
        $this->db->shouldReceive('insert')
            ->andThrow(DBALException::driverExceptionDuringQuery(new \Exception('unique key violation', 23505), 'sql'));
        $this->setExpectedException('Pvt\Exceptions\UniqueConstraintViolationException');
        $this->store->create('Test User', 'test@example.com', '123456');
    }

    public function testRethrowsOtherDbalExceptions()
    {
        $this->db->shouldReceive('insert')
            ->andThrow(new DBALException());
        $this->setExpectedException('Doctrine\DBAL\DBALException');
        $this->store->create('Test User', 'test@example.com', '123456');
    }
}
