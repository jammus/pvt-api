<?php

namespace Pvt\DataAccess;

interface UserStore
{
    /**
     * @param string $name User's full name.
     * @param string $email User's email address.
     * @param string $password User's plaintext password
     * @return int New user's id on success.
     */
    function create($name, $email, $password);

    /**
     * @param int $id User's id.
     *
     * @return \Pvt\Core\User
     */
    function fetchById($id);
}
