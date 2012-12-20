<?php

namespace Pvt\Interactors;

class CreateUserResult extends Result
{
    const INVALID_EMAIL = -1;
    const PASSWORD_TOO_SHORT = -2;
    const MISSING_NAME = -3;

    private $user;

    public function __construct(\Pvt\Core\User $user = null, $errors = array())
    {
        parent::__construct($errors);
        $this->user = $user;
    }

    public function user()
    {
        return $this->user;
    }
}
