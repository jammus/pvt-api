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
     *
     * @return SubmitPvtResultResult
     */
    public function execute($userId, $timestamp, $errorCount, Array $times)
    {
        $errors = array();

        $pvtResult = new PvtResult(
            $userId,
            $timestamp,
            $errorCount,
            $times
        );

        try {
            $this->store->save($pvtResult);
        } catch (UniqueConstraintViolationException $e) {
            $errors[] = SubmitPvtResultResult::DUPLICATE_SUBMISSION;
            $pvtResult = $this->store->getByUserIdAndTimestamp($userId, $timestamp);
        } catch (\Exception $e) {
            $errors[] = SubmitPvtResultResult::UNKNOWN_ERROR;
            $pvtResult = null;
        }

        return new SubmitPvtResultResult($pvtResult, $errors);
    }
}
