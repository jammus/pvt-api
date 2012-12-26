<?php

namespace Pvt\DataAccess;

interface UserStore
{
    function create($name, $email, $password);
    function fetchById($id);
}
