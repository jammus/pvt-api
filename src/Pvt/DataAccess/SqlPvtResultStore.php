<?php

namespace Pvt\DataAccess;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;

use Pvt\Core\PvtResult;
use Pvt\Exceptions\UniqueConstraintViolationException;

class SqlPvtResultStore implements PvtResultStore
{
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function fetchByUserIdAndTimestamp($userId, $timestamp)
    {
        $query = 'SELECT t1.*, t2.response_time ' .
                    'FROM ' .
                        'pvt_results AS t1, pvt_results_response_times AS t2 ' .
                    'WHERE ' .
                        't1.user_id = t2.user_id AND ' .
                        't1.timestamp = t2.timestamp AND ' .
                        't1.user_id = :user_id AND ' .
                        't1.timestamp = :timestamp ' .
                    'ORDER BY ' .
                        't2.sequence';

        $rows = $this->db->fetchAll(
            $query,
            array(
                'user_id' => $userId,
                'timestamp' => $timestamp
            )
        );

        if (!$rows) {
            return null;
        }

        return new PvtResult(
            $rows[0]['user_id'],
            $rows[0]['timestamp'],
            $rows[0]['error_count'],
            array_map(
                function ($row) {
                    return $row['response_time'];
                },
                $rows
            )
        );
    }

    public function save(PvtResult $pvtResult)
    {
        $this->db->transactional(
            function ($db) use ($pvtResult) {
                try {
                    $db->insert(
                        'pvt_results',
                        array(
                            'user_id' => $pvtResult->userId(),
                            'timestamp' => $pvtResult->date()->getTimestamp(),
                            'error_count' => $pvtResult->errors(),
                            'average_response_time' => $pvtResult->averageResponseTime()
                        )
                    );
                } catch (DBALException $e) {
                    $previous = $e->getPrevious();
                    if (isset($previous) && $previous->getCode() == 23505) {
                        throw new UniqueConstraintViolationException(
                            'Could not insert duplicate key of ' .
                                'user_id: ' . $pvtResult->userId() . ', ' .
                                'timestamp: ' . $pvtResult->date()->getTimestamp()
                        );
                    }
                    throw $e;
                }

                $responses = $pvtResult->responses();
                for ($index = 0; $index < count($responses); $index++) {
                    $db->insert(
                        'pvt_results_response_times',
                        array(
                            'user_id' => $pvtResult->userId(),
                            'timestamp' => $pvtResult->date()->getTimestamp(),
                            'sequence' => $index + 1,
                            'response_time' => $responses[$index]
                        )
                    );
                }
            }
        );
    }
}
