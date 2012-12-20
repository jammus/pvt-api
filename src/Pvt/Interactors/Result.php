<?php

namespace Pvt\Interactors;

class Result
{
    private $errors;

    public function __construct($errors = array())
    {
        if (! is_array($errors)) {
            $errors = $errors !== null ? array($errors) : array();
        }
        $this->errors = $errors;
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
