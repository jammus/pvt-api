<?php

namespace Pvt\Interactors;

use Pvt\DataAccess\AccessTokenStore;
use Pvt\DataAccess\UserStore;

class ValidateAccessToken
{
    private $tokenStore;

    private $userStore;

    public function __construct(AccessTokenStore $tokenStore, UserStore $userStore)
    {
        $this->tokenStore = $tokenStore;
        $this->userStore = $userStore;
    }

    public function validate($tokenString)
    {
        $errors = array();
        $user = null;
        $accessToken = null;

        $tokenString = trim($tokenString);

        if (mb_strlen($tokenString) === 0) {
            $errors[] = ValidateAccessTokenResult::INVALID;
        }
        if (empty($errors)) {
            $accessToken = $this->tokenStore->fetchByTokenString($tokenString);
            if ($accessToken === null) {
                $errors[] = ValidateAccessTokenResult::FALSE_OR_EXPIRED;
            }
        }
        if (empty($errors)) {
            $user = $this->userStore->fetchUserById($accessToken->userId());
        }
        return new ValidateAccessTokenResult($user, $accessToken, $errors);
    }
}
