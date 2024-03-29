<?php

namespace PvtTest\DataAccess;

use Doctrine\DBAL\DBALException;

use Pvt\Core\Password;

class SqlUserStoreCreateUserTest extends SqlUserStoreTestCase
{
    public function testInsertsDetails()
    {
        $this->db->expects($this->once())
            ->method('insert')
            ->with(
                'users',
                array(
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'password' => '123456',
                )
            );
        $this->store->create('Test User', 'test@example.com', new TestPassword('123456'));
    }

    public function testReturnsIdOnSuccess()
    {
        $this->db->expects($this->once())
            ->method('lastInsertId')
            ->with('users_id_seq')
            ->will($this->returnValue(1234));
        $id = $this->store->create('Test User', 'test@example.com', new TestPassword('123456'));
        $this->assertEquals(1234, $id);
    }

    public function testThrowsUniqueConstraintViolationExceptionOnDuplicateEmail()
    {
        $this->db->expects($this->once())
            ->method('insert')
            ->will($this->throwException(DBALException::driverExceptionDuringQuery(new \Exception('unique key violation', 23505), 'sql')));
        $this->setExpectedException('Pvt\Exceptions\UniqueConstraintViolationException');
        $this->store->create('Test User', 'test@example.com', new TestPassword('123456'));
    }

    public function testRethrowsOtherDbalExceptions()
    {
        $this->db->expects($this->once())
            ->method('insert')
            ->will($this->throwException(new DBALException()));
        $this->setExpectedException('Doctrine\DBAL\DBALException');
        $this->store->create('Test User', 'test@example.com', new TestPassword('123456'));
    }
}

class TestPassword extends Password
{
    private $hash;

    public function __construct($hash)
    {
        $this->hash = $hash;
    }

    public function hash()
    {
        return $this->hash;
    }
}
