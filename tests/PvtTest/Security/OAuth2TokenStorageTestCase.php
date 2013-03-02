<?php

namespace PvtTest\Security;

use Pvt\Security\OAuth2TokenStorage;

class OAuth2TokenStorageTestCase extends \PvtTest\PvtTestCase
{
    protected $accessTokenStore;

    protected $userStore;

    protected $storage;

    public function setup()
    {
        $this->accessTokenStore = $this->getMock('Pvt\DataAccess\AccessTokenStore');
        $this->userStore = $this->getMock('Pvt\DataAccess\UserStore');
        $this->storage = new OAuth2TokenStorage($this->userStore, $this->accessTokenStore);
    }
}
