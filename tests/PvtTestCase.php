<?php

namespace PvtTest;

class PvtTestCase extends \PHPUnit_Framework_TestCase
{
    public function teardown()
    {
        \Mockery::close();
    }
}
