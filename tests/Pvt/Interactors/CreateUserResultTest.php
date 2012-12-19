<?php

namespace PvtTest\Interactors;

use Pvt\Interactors\CreateUserResult;

class CreateUserResultTest extends \PvtTest\PvtTestCase
{
    public function testIsOkTrueWhenNoErrors()
    {
        $result = new CreateUserResult();
        $this->assertTrue($result->isOk());
    }

    public function testIsOkFalseWhenErrorsSpecified()
    {
        $result = new CreateUserResult(null, CreateUserResult::INVALID_EMAIL);
        $this->assertFalse($result->isOk());
    }

    public function testHasErrorFalseByDefault()
    {
        $result = new CreateUserResult();
        $this->assertFalse($result->hasError(CreateUserResult::INVALID_EMAIL));
    }

    public function testHasErrorWhenContainsSpecifiedError()
    {
        $result = new CreateUserResult(null, CreateUserResult::INVALID_EMAIL);
        $this->assertTrue($result->hasError(CreateUserResult::INVALID_EMAIL));
    }

    public function testResultCanContainMultipleErrors()
    {
        $result = new CreateUserResult(null, array(
            CreateUserResult::INVALID_EMAIL,
            CreateUSerResult::PASSWORD_TOO_SHORT
        ));
        $this->assertFalse($result->isOk());
        $this->assertTrue($result->hasError(CreateUserResult::INVALID_EMAIL));
        $this->assertFalse($result->hasError(CreateUserResult::MISSING_NAME));
        $this->assertTrue($result->hasError(CreateUserResult::PASSWORD_TOO_SHORT));
    }
}
