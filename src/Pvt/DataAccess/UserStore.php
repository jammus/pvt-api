<?php

namespace Pvt\DataAccess;

use Pvt\Core\Password;

interface UserStore
{
    /**
     * @param string $name User's full name.
     * @param string $email User's email address.
     * @param Pvt\Core\Password $password User's password
     *
     * @return int New user's id on success.
     */
    public function create($name, $email, Password $password);

    /**
     * @param int $id User's id.
     *
     * @return \Pvt\Core\User
     */
    public function fetchById($id);

    /**
     * @param string $email User's email address
     *
     * @return \Pvt\Core\User|null
     */
    public function fetchByEmail($email);
}
