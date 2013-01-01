<?php

namespace Pvt\DataAccess;

use Pvt\Core\PvtResult;

interface PvtResultStore
{
    /**
     * @param Pvt\Core\PvtResult $result
     *
     * @return Pvt\Core\PvtResult
     */
    public function save(PvtResult $result);

    /**
     * @param int $userId
     * @param int $timestamp
     *
     * @return Pvt\Core\PvtResult|null
     */
    public function fetchByUserIdAndTimestamp($userId, $timestamp);
}
