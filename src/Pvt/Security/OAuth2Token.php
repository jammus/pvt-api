<?php

namespace Pvt\Security;

use OAuth2\Model\IOAuth2Token;

use Pvt\Core\AccessToken;

class OAuth2Token extends AccessToken implements IOAuth2Token
{
    private $accessToken;

    public function __construct(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getClientId()
    {
        return 'android';
    }

    public function getExpiresIn()
    {
        return PHP_INT_MAX;
    }
    
    public function hasExpired()
    {
        return false;
    }

    public function getToken()
    {
        return $this->accessToken->token;
    }

    public function getScope()
    {
    }

    public function getData()
    {
        return $this->accessToken->userId;
    }
}
