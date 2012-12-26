<?php

namespace PvtTest\Interactors;

use Pvt\Core\User;
use Pvt\DataAccess\UserStore;
use Pvt\Exceptions\DuplicateUserException;
use Pvt\Exceptions\UniqueConstraintViolationException;
use Pvt\Interactors\CreateUser;
use Pvt\Interactors\CreateUserResult;

class CreateUserTest extends \PvtTest\PvtTestCase
{
    public function setup()
    {
        parent::setup();
        $this->userstore = $this->getPartialMock('Pvt\DataAccess\UserStore[create,fetchById]');
        $this->interactor = new CreateUser($this->userstore);
    }

    public function testReturnsInvalidEmailResultWhenEmailMalformed()
    {
        $result = $this->interactor->execute('name', 'email', 'password');
        $this->assertTrue($result->hasError(CreateUserResult::INVALID_EMAIL));

        $result = $this->interactor->execute('name', 'email@somethingcom', 'password');
        $this->assertTrue($result->hasError(CreateUserResult::INVALID_EMAIL));

        $result = $this->interactor->execute('name', 'emailsomethingcom.www', 'password');
        $this->assertTrue($result->hasError(CreateUserResult::INVALID_EMAIL));
    }

    public function testReturnsInvalidPasswordWhenTooShort()
    {
        $result = $this->interactor->execute('name', 'test@example.com', '');
        $this->assertTrue($result->hasError(CreateUserResult::PASSWORD_TOO_SHORT));

        $result = $this->interactor->execute('name', 'test@example.com', '1           ');
        $this->assertTrue($result->hasError(CreateUserResult::PASSWORD_TOO_SHORT));
    }

    public function testReturnsInvalidPasswordWhenBlankOrMissing()
    {
        $result = $this->interactor->execute('name', 'test@example.com', '');
        $this->assertTrue($result->hasError(CreateUserResult::PASSWORD_TOO_SHORT));

        $result = $this->interactor->execute('name', 'test@example.com', '            ');
        $this->assertTrue($result->hasError(CreateUserResult::PASSWORD_TOO_SHORT));

        $result = $this->interactor->execute('name', 'test@example.com', null);
        $this->assertTrue($result->hasError(CreateUserResult::PASSWORD_TOO_SHORT));
    }

    public function testReturnsMissingNameWhenOmitted()
    {
        $result = $this->interactor->execute(' ', 'test@example.com', 'balalalal');
        $this->assertTrue($result->hasError(CreateUserResult::MISSING_NAME));

        $result = $this->interactor->execute('', 'test@example.com', 'balalalal');
        $this->assertTrue($result->hasError(CreateUserResult::MISSING_NAME));

        $result = $this->interactor->execute(null, 'test@example.com', 'balalalal');
        $this->assertTrue($result->hasError(CreateUserResult::MISSING_NAME));
    }

    public function testPassesThroughCreationToUserStore()
    {
        $name = 'Test User';
        $email = 'test@example.com';
        $password = 'sufficiently long password';
        $this->userstore->shouldReceive('create')
            ->with($name, $email, $password)
            ->once();
        $this->userstore->shouldReceive('fetchById')->andReturn(new User('', '', '')); // required as it otherwise returns a mock object which confuses the result class
        $this->interactor->execute($name, $email, $password);
    }

    public function testFetchesAndReturnsUserOnSuccess()
    {
        $name = 'Test User';
        $email = 'test@example.com';
        $password = 'sufficiently long password';
        $user = new User(1234, $name, $email);
        $this->userstore->shouldReceive('create')
            ->andReturn(1234)
            ->once();
        $this->userstore->shouldReceive('fetchById')
            ->with(1234)
            ->andReturn($user)
            ->once();
        $result = $this->interactor->execute($name, $email, $password);
        $this->assertEquals($user, $result->user());
    }

    public function testThrowsDuplicateUserExceptionOnUniqueConstraint()
    {
        $this->userstore->shouldReceive('create')
            ->andThrow(new UniqueConstraintViolationException());
        $this->setExpectedException('Pvt\Exceptions\DuplicateUserException');
        $this->interactor->execute('blah', 'blah@blah.com', 'blahblah');
    }
}