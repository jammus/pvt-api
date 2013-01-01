<?php

namespace PvtTest\DataAccess;

use Pvt\DataAccess\SqlAccessTokenStore;

abstract class SqlAccessTokenStoreTestCase extends \PvtTest\PvtDatabaseTestCase
{
    protected $store;

    public function setup()
    {
        parent::setup();
        $this->store = new SqlAccessTokenStore($this->db);
    }
}
