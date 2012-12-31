<?php

namespace Pvt\Interactors;

class Result
{
    const UNKNOWN_ERROR = 0;

    private $errors;

    /**
     * @param int|int[] $errors A single error id or an array of errors.
     */
    public function __construct($errors = array())
    {
        if (! is_array($errors)) {
            $errors = $errors !== null ? array($errors) : array();
        }
        $this->errors = $errors;
    }

    /**
     * @return boolean True if the result contains no errors.
     */
    public function isOk()
    {
        return empty($this->errors);
    }

    /**
     * @return boolean True if the specified errors is contained within
     * the result.
     */
    public function hasError($error)
    {
        return in_array($error, $this->errors);
    }
}
