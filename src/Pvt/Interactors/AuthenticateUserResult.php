<?php

namespace Pvt\Interactors;

use Pvt\Core\AccessToken;
use Pvt\Core\User;

class AuthenticateUserResult extends Result
{
    const INVALID_EMAIL = -1;
    const INVALID_PASSWORD = -2;
    const INVALID_ACCESS_TOKEN = -3;
    const FALSE_OR_EXPIRED_ACCESS_TOKEN = -4;

    private $user;

    public function __construct(User $user = null, AccessToken $accessToken = null, $errors = array())
    {
        parent::__construct($errors);
        $this->user = $user;
        $this->accessToken = $accessToken;
    }

    public function user()
    {
        return $this->user;
    }

    public function accessToken()
    {
        return $this->accessToken;
    }
}
