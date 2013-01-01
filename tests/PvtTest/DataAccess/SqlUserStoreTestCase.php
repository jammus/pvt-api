<?php

namespace PvtTest\DataAccess;

use Pvt\DataAccess\SqlUserStore;

abstract class SqlUserStoreTestCase extends \PvtTest\PvtDatabaseTestCase
{
    protected $store;

    public function setup()
    {
        parent::setup();
        $this->store = new SqlUserStore($this->db);
    }
}
