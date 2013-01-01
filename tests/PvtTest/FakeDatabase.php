<?php

namespace PvtTest;

use Doctrine\DBAL\Connection;

class FakeDatabase extends Connection
{
    private $transaction;

    public function transactional($transaction)
    {
        $this->transaction = $transaction;
    }

    public function doTransaction()
    {
        call_user_func_array($this->transaction, array($this));
    }
}
