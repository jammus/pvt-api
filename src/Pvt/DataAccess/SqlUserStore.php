<?php

namespace Pvt\DataAccess;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Connection;

use Pvt\Core\Password;
use Pvt\Core\User;
use Pvt\Exceptions\UniqueConstraintViolationException;

class SqlUserStore implements UserStore
{
    private $db;

    /**
     * @param \Doctrine\DBAL\Connection Database connection
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @throws UniqueConstraintViolationException if duplicate user exists in data store.
     */
    public function create($name, $email, Password $password)
    {
        try {
            $this->db->insert(
                'users',
                array(
                    'name' => $name,
                    'email' => $email,
                    'password' => $password->hash(),
                )
            );
        } catch (DBALException $e) {
            $previous = $e->getPrevious();
            if (isset($previous) && $previous->getCode() == 23505) {
                throw new UniqueConstraintViolationException("Could not insert duplicate row: $name, $email, ********");
            }
            throw $e;
        }

        return $this->db->lastInsertId('users_id_seq');
    }

    /**
     * @param int $id
     *
     * @return Pvt\Core\User
     */
    public function fetchById($id)
    {
        return $this->fetchUser(
            'SELECT * FROM users WHERE id = :id',
            array('id' => $id)
        );
    }

    /**
     * @param string $email
     *
     * @return Pvt\Core\User
     */
    public function fetchByEmail($email)
    {
        return $this->fetchUser(
            'SELECT * FROM users WHERE email = :email',
            array('email' => $email)
        );
    }

    private function fetchUser($query, Array $params)
    {
        $result = $this->db->fetchAssoc(
            $query,
            $params
        );

        if (!$result) {
            return null;
        }

        return new User(
            $result['id'],
            $result['name'],
            $result['email'],
            Password::fromHash($result['password'])
        );
    }
}
