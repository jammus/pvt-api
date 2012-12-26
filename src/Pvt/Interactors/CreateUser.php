<?php

namespace Pvt\Interactors;

use Pvt\Core\User;
use Pvt\DataAccess\UserStore;
use Pvt\Exceptions\DuplicateUserException;
use Pvt\Exceptions\UniqueConstraintViolationException;

class CreateUser
{
    private $userstore;

    public function __construct(UserStore $userstore)
    {
        $this->userstore = $userstore;
    }

    public function create($name, $email, $password)
    {
        $password = trim($password);
        $name = trim($name);
        $email = trim($email);

        $errors = $this->validateInput($name, $email, $password);
        if ($errors) {
            return new CreateUserResult(null, $errors);
        }
        try {
            $id = $this->userstore->create($name, $email, $password);
        }
        catch (UniqueConstraintViolationException $e) {
            throw new DuplicateUserException('Cannot create user with email: ' . $email);
        }
        $user = $this->userstore->fetchById($id);
        return new CreateUserResult($user);
    }

    private function validateInput($name, $email, $password)
    {
        $errors = array();
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = CreateUserResult::INVALID_EMAIL;
        }
        if (mb_strlen($password) < 8) {
            $errors[] = CreateUserResult::PASSWORD_TOO_SHORT;
        }
        if (mb_strlen($name) === 0) {
            $errors[] = CreateUserResult::MISSING_NAME;
        }
        return $errors;
    }
}
