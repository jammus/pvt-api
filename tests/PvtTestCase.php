<?php

namespace PvtTest;

class PvtTestCase extends \PHPUnit_Framework_TestCase
{
    public function teardown()
    {
        \Mockery::close();
    }

    protected function getPartialMock($definition)
    {
        $mock = \Mockery::mock($definition);
        $mock->shouldIgnoreMissing();
        return $mock;
    }
}
