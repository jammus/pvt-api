<?php

namespace Pvt\DataAccess;

use Doctrine\DBAL\Connection;

use Pvt\Core\AccessToken;

class SqlAccessTokenStore implements AccessTokenStore
{
    private $db;

    /**
     * @param \Doctrine\DBAL\Connection Database connection
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function fetchByTokenString($token)
    {
        $query = 'SELECT * FROM access_tokens WHERE access_token = :access_token';
        $result = $this->db->fetchAssoc($query, array('access_token' => $token));
        if (!$result) {
            return null;
        }
        return new AccessToken(
            $result['user_id'],
            $result['access_token']
        );
    }

    public function fetchByUserId($userId)
    {
    }

    public function save(AccessToken $accessToken)
    {
    }
}
