<?php

namespace Pvt\Security;

class OAuth2Client implements \OAuth2\Model\IOAuth2Client
{
    public function getPublicId()
    {
        return 'android';
    }

    public function getRedirectUris()
    {
    }
}
