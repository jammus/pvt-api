<?php

namespace Pvt\Interactors;

use Pvt\Core\AccessToken;
use Pvt\DataAccess\AccessTokenStore;
use Pvt\DataAccess\UserStore;

/**
 * Exchange an email address and password for user and access token
 */
class AuthenticateUserWithPassword
{
    private $userStore;

    private $accessTokenStore;

    public function __construct(UserStore $userStore, AccessTokenStore $accessTokenStore)
    {
        $this->userStore = $userStore;
        $this->accessTokenStore = $accessTokenStore;
    }

    /**
     * Validate a user based on the email and password supplied.
     *
     * @param string $email Email address of user to be authenticated
     * @param string $password Password used for authentication
     *
     * @return AuthenticateUserResult
     */
    public function execute($email, $password)
    {
        $errors = array();

        $user = $this->userStore->fetchByEmail($email);
        $accessToken = null;

        if (!$user) {
            $errors[] = AuthenticateUserResult::INVALID_EMAIL;
        }

        if ($user && !$user->checkPassword($password)) {
            $errors[] = AuthenticateUserResult::INVALID_PASSWORD;
            $user = null;
        }

        if ($user) {
            $accessToken = $this->accessTokenStore->fetchByUserId($user->id());
        }

        if ($user && !$accessToken) {
            $accessToken = AccessToken::forUserId($user->id());
            $this->accessTokenStore->save($accessToken);
        }

        return new AuthenticateUserResult($user, $accessToken, $errors);
    }
}
