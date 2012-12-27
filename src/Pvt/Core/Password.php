<?php

namespace Pvt\Core;

use Hautelook\Phpass\PasswordHash;

class Password
{
    private $hash;

    private function __construct($hash)
    {
        $this->hash = $hash;
    }

    public static function fromPlainText($password)
    {
        if (mb_strlen($password) === 0) {
            throw new \InvalidArgumentException();
        }
        $hasher = new PasswordHash(10, false);
        return new Password($hasher->HashPassword($password));
    }

    public static function fromHash($hash)
    {
        return new Password($hash);
    }

    public function hash()
    {
        return $this->hash;
    }

    public function matches($password)
    {
        $hasher = new PasswordHash(10, false);
        return $hasher->CheckPassword('password', $this->hash);
    }
}
