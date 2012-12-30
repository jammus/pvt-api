<?php

namespace Pvt\Interactors;

use Pvt\Core\Password;
use Pvt\Core\User;
use Pvt\DataAccess\UserStore;
use Pvt\Exceptions\UniqueConstraintViolationException;

/**
 * Create a new user record
 */
class CreateUser
{
    private $userstore;

    public function __construct(UserStore $userstore)
    {
        $this->userstore = $userstore;
    }

    /**
     *
     * @param string $name User's full name.
     * @param string $email User's email address.
     * @param string $password User's desired plaintext password
     *
     * @return CreateUserResult
     */
    public function execute($name, $email, $password)
    {
        $password = trim($password);
        $name = trim($name);
        $email = trim($email);

        $user = null;

        $errors = $this->validateInput($name, $email, $password);
        if ($errors) {
            return new CreateUserResult($user, $errors);
        }

        try {
            $id = $this->userstore->create($name, $email, Password::fromPlainText($password));
        } catch (UniqueConstraintViolationException $e) {
            $errors[] = CreateUserResult::DUPLICATE_USER;
        }

        if (isset($id) && empty($errors)) {
            $user = $this->userstore->fetchById($id);
        }

        return new CreateUserResult($user, $errors);
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
