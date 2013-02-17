<?php

namespace PvtTest\Interactors;

use Pvt\Core\PvtResult;
use Pvt\Exceptions\UniqueConstraintViolationException;
use Pvt\Interactors\SubmitPvtResult;
use Pvt\Interactors\SubmitPvtResultResult;

class SubmitPvtResultTest extends \PvtTest\PvtTestCase
{
    public function setup()
    {
        $this->store = $this->getMock('\Pvt\DataAccess\PvtResultStore');
        $this->interactor = new SubmitPvtResult($this->store);
    }

    public function testSavesDetailsAsPvtResult()
    {
        $expectedPvtResult = new PvtResult(10001, 1234567890, array(
            123.45, 234.56, 345.67, 456.78
        ));

        $this->store->expects($this->once())
            ->method('save')
            ->with($this->equalTo($expectedPvtResult));
        
        $this->interactor->execute(10001, 1234567890, array(
            123.45, 234.56, 345.67, 456.78
        ));
    }

    public function testReturnsSubmitPvtResultResult()
    {
        $result = $this->interactor->execute(10001, 1234567890, array(
            123.45, 234.56, 345.67, 456.78
        ));
        $this->assertInstanceOf('\Pvt\Interactors\SubmitPvtResultResult', $result);
    }

    public function testResultIsOkWhenSuccessful()
    {
        $result = $this->interactor->execute(1001, 1234667879, array());
        $this->assertTrue($result->isOk());
    }

    public function testResultIncludesResultWhenSuccessful()
    {
        $expectedPvtResult = new PvtResult(10001, 1234567890, array(
            123.45, 234.56, 345.67, 456.78
        ));

        $result = $this->interactor->execute(10001, 1234567890, array(
            123.45, 234.56, 345.67, 456.78
        ));

        $this->assertEquals($expectedPvtResult, $result->pvtResult());
    }

    public function testResultIncludesAlreadySubmittedWhenDuplicateReport()
    {
        $this->store->expects($this->any())
            ->method('save')
            ->will($this->throwException(new UniqueConstraintViolationException()));

        $result = $this->interactor->execute(1001, 1234667879, array());

        $this->assertTrue($result->hasError(SubmitPvtResultResult::DUPLICATE_SUBMISSION));
    }

    public function testLookUpExistingResultWhenDuplicateSubmission()
    {
        $this->store->expects($this->any())
            ->method('save')
            ->will($this->throwException(new UniqueConstraintViolationException()));

        $this->store->expects($this->once())
            ->method('fetchByUserIdAndTimestamp')
            ->with(10001, 1234567890);

        $result = $this->interactor->execute(10001, 1234567890, array(
            123.45, 234.56, 345.67, 456.78
        ));
    }

    public function testResultIncludesExistingResultWhenDuplicateSubmission()
    {
        $existingPvtResult = new PvtResult(10001, 1234567890, array(
            234.56, 345.67, 456.78, 567.89
        ));

        $this->store->expects($this->any())
            ->method('save')
            ->will($this->throwException(new UniqueConstraintViolationException()));
        $this->store->expects($this->any())
            ->method('fetchByUserIdAndTimestamp')
            ->will($this->returnValue($existingPvtResult));

        $result = $this->interactor->execute(10001, 1234567890, array(
            123.45, 234.56, 345.67, 456.78
        ));

        $this->assertEquals($existingPvtResult, $result->pvtResult());
    }

    public function testResultIncludesUnknownErrorWhenCouldNotSave()
    {
        $this->store->expects($this->any())
            ->method('save')
            ->will($this->throwException(new \Exception()));

        $result = $this->interactor->execute(10001, 1234567890, array());

        $this->assertTrue($result->hasError(SubmitPvtResultResult::UNKNOWN_ERROR));
    }

    public function testResultDoesNotIncludePvtResultWhenCouldNotSave()
    {
        $this->store->expects($this->any())
            ->method('save')
            ->will($this->throwException(new \Exception()));

        $result = $this->interactor->execute(10001, 1234567890, array());

        $this->assertNull($result->pvtResult());
    }
}
