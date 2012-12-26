<?php

namespace Pvt\Interactors;

use Pvt\DataAccess\AccessTokenStore;
use Pvt\DataAccess\UserStore;

/**
 * Exchange a access token string for full details and the associated
 * user
 */
class ValidateAccessToken
{
    private $tokenStore;

    private $userStore;

    public function __construct(AccessTokenStore $tokenStore, UserStore $userStore)
    {
        $this->tokenStore = $tokenStore;
        $this->userStore = $userStore;
    }

    /**
     * Validate a user based on the access token string supplied.
     *
     * @param string $tokenString The access token string as presented on
     * authentication.
     *
     * @return ValidateAccessTokenResult Result includes the full access token
     * details and associated user if successful.
     */
    public function execute($tokenString)
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
            $user = $this->userStore->fetchById($accessToken->userId());
        }
        return new ValidateAccessTokenResult($user, $accessToken, $errors);
    }
}
