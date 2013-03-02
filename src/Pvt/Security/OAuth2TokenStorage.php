<?php

namespace Pvt\Security;

use OAuth2\IOAuth2Storage;
use OAuth2\IOAuth2GrantUser;
use OAuth2\Model\IOAuth2Client;

use Pvt\Core\AccessToken;
use Pvt\DataAccess\AccessTokenStore;
use Pvt\DataAccess\UserStore;

class OAuth2TokenStorage implements IOAuth2Storage, IOAuth2GrantUser
{
    private $userStore;

    private $accessTokenStore;

    public function __construct(UserStore $userStore, AccessTokenStore $accessTokenStore)
    {
        $this->userStore = $userStore;
        $this->accessTokenStore = $accessTokenStore;
    }

    public function getClient($client_id)
    {
        return new OAuth2Client();
    }

	public function checkClientCredentials(IOAuth2Client $client, $client_secret = NULL)
    {
        return $client->getPublicId() === 'android' &&
            $client_secret === '8hska3hjo320iola-28ihj2/23973owld';
    }

	public function getAccessToken($oauth_token)
    {
        $token = $this->accessTokenStore->fetchByTokenString($oauth_token);

        if ( ! $token) {
            return null;
        }
        
        return new OAuth2Token($token);
    }

	public function createAccessToken($oauth_token, IOAuth2Client $client, $data, $expires, $scope = NULL)
    {
        $token = new AccessToken($data, $oauth_token);
        
        $this->accessTokenStore->save($token);
    }

	public function checkRestrictedGrantType(IOAuth2Client $client, $grant_type)
    {
        return true;
    }
    
    public function checkUserCredentials(IOAuth2Client $client, $username, $password)
    {
        $user = $this->userStore->fetchByEmail($username);
        return $user && $user->checkPassword($password);
    }
}
