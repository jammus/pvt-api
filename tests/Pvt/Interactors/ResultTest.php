<?php

namespace PvtTest\Interactors;

use Pvt\Interactors\Result;

class ResultTest extends \PvtTest\PvtTestCase
{
    const ERROR_TYPE_ONE = -1;
    const ERROR_TYPE_TWO = -2;
    const ERROR_TYPE_ELEVEN = -3;

    public function testIsOkTrueWhenNoErrors()
    {
        $result = new Result();
        $this->assertTrue($result->isOk());
    }

    public function testIsOkFalseWhenErrorsSpecified()
    {
        $result = new Result(self::ERROR_TYPE_ONE);
        $this->assertFalse($result->isOk());
    }

    public function testHasErrorFalseByDefault()
    {
        $result = new Result();
        $this->assertFalse($result->hasError(self::ERROR_TYPE_ONE));
    }

    public function testHasErrorWhenContainsSpecifiedError()
    {
        $result = new Result(self::ERROR_TYPE_ONE);
        $this->assertTrue($result->hasError(self::ERROR_TYPE_ONE));
    }

    public function testResultCanContainMultipleErrors()
    {
        $result = new Result(array(
            self::ERROR_TYPE_ONE,
            self::ERROR_TYPE_TWO
        ));
        $this->assertFalse($result->isOk());
        $this->assertTrue($result->hasError(self::ERROR_TYPE_ONE));
        $this->assertFalse($result->hasError(self::ERROR_TYPE_ELEVEN));
        $this->assertTrue($result->hasError(self::ERROR_TYPE_TWO));
    }

    public function testNullIsNotConsideredAnError()
    {
        $result = new Result(null);
        $this->assertTrue($result->isOk());
    }
}
