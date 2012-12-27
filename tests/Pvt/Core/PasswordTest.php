<?php

namespace PvtTest\Core;

use Hautelook\Phpass\PasswordHash;

use Pvt\Core\Password;

class PasswordTest extends \PvtTest\PvtTestCase
{
    public function testPasswordThrowsInvalidArgumentExceptionIfPasswordIsBlank()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $password = Password::fromPlainText('');
    }

    public function testHashWrapsPhpassHashing()
    {
        $hasher = new PasswordHash(8, false);
        $password = Password::fromPlainText('password');
        $hash = $password->hash();
        $this->assertTrue($hasher->CheckPassword('password', $hash));
    }

    public function testHashesAreIterated10Times()
    {
        $password = Password::fromPlainText('password');
        $hash = $password->hash();
        $parts = split('\$', $hash);
        $this->assertEquals(10, $parts[2]);
    }

    public function testFromHashDoesNotAlterPassword()
    {
        $password = Password::fromHash('dasdsadsadsa');
        $this->assertEquals('dasdsadsadsa', $password->hash());
    }

    public function testPasswordMatchesSamePlainText()
    {
        $password = Password::fromPlainText('password');
        $this->assertTrue($password->matches('password'));
    }

    public function testPasswordDoesNotMatchDifferentPlainText()
    {
        $password = Password::fromPlainText('pissword');
        $this->assertFalse($password->matches('password'));

        $password = Password::fromPlainText('password');
        $this->assertFalse($password->matches('pissword'));
    }

    public function testPasswordDoesNotMatchRandomHash()
    {
        $password = Password::fromHash('hashhashhash');
        $this->assertFalse($password->matches('password'));
    }

    public function testPasswordMatchesHashGeneratedByPhpass()
    {
        $password = Password::fromHash('$2a$10$Nfop43.5bbzmndx2b1cTgOK4OOIE3qnV9fbZRwifUQX91rMu.zLjW');
        $this->assertTrue($password->matches('password'));
    }
}
