<?php

namespace Pvt\DataAccess;

use Pvt\Core\PvtResult;

interface PvtResultStore
{
    public function save(PvtResult $result);

    public function getByUserIdAndTimestamp($userId, $timestamp);
}
