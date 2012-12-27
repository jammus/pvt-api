<?php

namespace Pvt\DataAccess;

use Pvt\Core\AccessToken;

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

    /**
     * @param \Pvt\Core\AccessToken $accessToken
     */
    public function save(AccessToken $accessToken);
}
