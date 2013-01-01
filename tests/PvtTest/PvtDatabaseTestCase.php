<?php

namespace PvtTest;

abstract class PvtDatabaseTestCase extends PvtTestCase
{
    protected $db;

    public function setup()
    {
        $this->db = $this->getMockBuilder('\PvtTest\FakeDatabase')
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'insert',
                    'lastInsertId',
                    'fetchAssoc',
                    'fetchAll',
                )
            )
            ->getMock();
    }
}
