<?php

namespace PvtTest\DataAccess;

use Pvt\DataAccess\SqlUserStore;

class SqlUserStoreTestCase extends \PvtTest\PvtTestCase
{
    protected $db;

    protected $store;

    public function setup()
    {
        $this->db = $this->getMockBuilder('\Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->store = new SqlUserStore($this->db);
    }
}
