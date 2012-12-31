<?php

namespace Pvt\Interactors;

use Pvt\Core\PvtResult;
use Pvt\DataAccess\PvtResultStore;
use Pvt\Exceptions\UniqueConstraintViolationException;

class SubmitPvtResult
{
    private $store;

    public function __construct(PvtResultStore $store)
    {
        $this->store = $store;
    }

    /**
     * @param int $userId
     * @param int $timestamp
     * @param int $errorCount
     * @param array[]float $time
     */
    public function execute($userId, $timestamp, $errorCount, Array $times)
    {
        $errors = array();

        $result = new PvtResult(
            $userId,
            $timestamp,
            $errorCount,
            $times
        );

        try {
            $this->store->save($result);
        } catch (UniqueConstraintViolationException $e) {
            $errors[] = SubmitPvtResultResult::DUPLICATE_SUBMISSION;
            $result = $this->store->getByUserIdAndTimestamp($userId, $timestamp);
        }

        return new SubmitPvtResultResult($result, $errors);
    }
}
