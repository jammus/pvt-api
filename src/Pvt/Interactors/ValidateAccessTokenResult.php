<?php

namespace Pvt\Interactors;

use Pvt\Core\AccessToken;
use Pvt\Core\User;

class ValidateAccessTokenResult extends Result
{
    const INVALID = -1;
    const FALSE_OR_EXPIRED = -2;

    private $accessToken;

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
