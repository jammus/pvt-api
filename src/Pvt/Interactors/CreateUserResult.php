<?php

namespace Pvt\Interactors;

class CreateUserResult
{
    const INVALID_EMAIL = -1;
    const PASSWORD_TOO_SHORT = -2;
    const MISSING_NAME = -3;

    private $user;

    private $errors;

    public function __construct(\Pvt\Core\User $user = null, $errors = array())
    {
        $this->user = $user;
        if (! is_array($errors)) {
            $errors = array($errors);
        }
        $this->errors = $errors;
    }

    public function user()
    {
        return $this->user;
    }

    public function isOk()
    {
        return empty($this->errors);
    }

    public function hasError($error)
    {
        return in_array($error, $this->errors);
    }
}
