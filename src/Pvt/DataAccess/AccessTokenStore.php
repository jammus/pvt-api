<?php

namespace Pvt\DataAccess;

interface AccessTokenStore
{
    /**
     * @param string $tokenString
     *
     * @return null|\Pvt\Core\AccessToken
     */
    public function fetchByTokenString($tokenString);

    /**
     * @param int $userId
     *
     * @return null|\Pvt\Core\AccessToken
     */
    public function fetchByUserId($userId);
}
