<?php

namespace Pvt\DataAccess;

interface AccessTokenStore
{
    function fetchByTokenString($token);
}
