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
        return $this->fetchAccessToken(
            'SELECT * FROM access_tokens WHERE access_token = :access_token',
            array('access_token' => $token)
        );
    }

    public function fetchByUserId($userId)
    {
        return $this->fetchAccessToken(
            'SELECT * FROM access_tokens WHERE user_id = :user_id',
            array('user_id' => $userId)
        );
    }

    public function save(AccessToken $accessToken)
    {
    }

    private function fetchAccessToken($query, Array $params)
    {
        $result = $this->db->fetchAssoc(
            $query,
            $params
        );

        if (!$result) {
            return null;
        }

        return new AccessToken(
            $result['user_id'],
            $result['access_token']
        );
    }
}
