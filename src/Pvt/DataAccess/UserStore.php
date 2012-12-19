<?php

namespace Pvt\DataAccess;

interface UserStore
{
    function createUser($name, $email, $password);
    function fetchUserById($id);
}
