<?php

namespace Pvt\Core;

class AccessToken
{
    protected $userId;

    protected $token;

    const EPOCH = 1356645562;

    public function __construct($userId, $token)
    {
        $this->userId = $userId;
        $this->token = $token;
    }

    /**
     * Generate new access token for user
     *
     * @return Pvt\Core\AccessToken
     */
    public static function forUserId($userId)
    {
        $version = '00';

        $genTime = (time() - self::EPOCH);
        $salt = substr(md5(mt_rand() . getmypid()), 0, 6);
        $hash = sha1($salt . md5('SAD.K3ijksa=9$hjcsa9240/2/u3' . $version . $userId . $genTime));

        $rawToken = $version . $genTime . '.' . $salt . '/' . $hash;

        return new AccessToken($userId, base64_encode($rawToken));
    }

    public function userId()
    {
        return $this->userId;
    }

    public function token()
    {
        return $this->token;
    }
}
