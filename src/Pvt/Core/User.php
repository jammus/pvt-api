<?php

namespace Pvt\Core;

class User
{
    private $id;

    private $name;

    private $email;

    private $password;

    public function __construct($id, $name, $email, Password $password = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
    }

    /**
     * @return int
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function email()
    {
        return $this->email;
    }

    /**
     * @return string Relative url of user's profile
     */
    public function profileUrl()
    {
        return '/users/' . $this->id;
    }

    public function checkPassword($password)
    {
        if ($this->password === null) {
            return false;
        }
        return $this->password->matches($password);
    }
}
