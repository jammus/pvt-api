<?php

namespace Pvt\DataAccess;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Connection;

use Pvt\Core\User;
use Pvt\Exceptions\UniqueConstraintViolationException;

class SqlUserStore implements UserStore
{
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function createUser($name, $email, $password)
    {
        try {
            $result = $this->db->insert(
                'users',
                array(
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                )
            );
        }
        catch (DBALException $e) {
            $previous = $e->getPrevious();
            if (isset($previous) && $previous->getCode() == 23505) {
                throw new UniqueConstraintViolationException("Could not insert duplicate row: $name, $email, ********");
            }
            throw $e;
        }
        return $this->db->lastInsertId('users_id_seq');
    }

    public function fetchUserById($id)
    {
        $result = $this->db->fetchAssoc(
            'SELECT * FROM users WHERE id = :id',
            array('id' => $id)
        );
        return new User(
            $result['id'],
            $result['name'],
            $result['email']
        );
    }
}
