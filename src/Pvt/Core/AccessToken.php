<?php

namespace Pvt\Core;

class AccessToken
{
    private $userId;

    private $tokenString;

    public function __construct($userId, $tokenString)
    {
        $this->userId = $userId;
        $this->tokenString = $tokenString;
    }

    public function userId()
    {
        return $this->userId;
    }
}
