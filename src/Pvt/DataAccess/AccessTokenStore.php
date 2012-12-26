<?php

namespace Pvt\DataAccess;

interface AccessTokenStore
{
    /**
     * @param string $tokenString
     *
     * @return null|\Pvt\Core\AccessToken
     */
    function fetchByTokenString($tokenString);

    /**
     * @param int $userId
     *
     * @return null|\Pvt\Core\AccessToken
     */
    function fetchByUserId($userId);
}
