<?php

namespace PvtTest\DataAccess;

use Pvt\DataAccess\SqlAccessTokenStore;

abstract class SqlAccessTokenStoreTestCase extends \PvtTest\PvtTestCase
{
    protected $db;

    protected $store;

    public function setup()
    {
        parent::setup();
        $this->db = $this->getMockBuilder('\Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->store = new SqlAccessTokenStore($this->db);
    }
}
