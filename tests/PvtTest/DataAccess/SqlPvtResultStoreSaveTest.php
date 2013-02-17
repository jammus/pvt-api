<?php

namespace PvtTest\DataAccess;

use Doctrine\DBAL\DBALException;

use Pvt\Core\PvtResult;
use Pvt\DataAccess\SqlPvtResultStore;

class SqlPvtResultStoreSaveTest extends \PvtTest\PvtDatabaseTestCase
{
    private $store;

    public function setup()
    {
        parent::setup();
        $this->store = new SqlPvtResultStore($this->db);
    }

    public function testInsertsWrappedInATransaction()
    {
        $pvtResult = new PvtResult(10001, 1234567890, 5, array(1.1, 2.2, 3.3, 4.4, 5.5));
        $this->db->expects($this->never())
            ->method('insert');
        $this->store->save($pvtResult);
    }

    public function testInsertsTestDetailsDetails()
    {
        $pvtResult = new PvtResult(10001, 1234567890, 5, array(1.1, 2.2, 3.3, 4.4, 5.5));
        $this->db->expects($this->at(0))
            ->method('insert')
            ->with(
                'pvt_results',
                array(
                    'user_id' => 10001,
                    'timestamp' => 1234567890,
                    'error_count' => 5,
                    'average_response_time' => 3.3
                )
            );
        $this->store->save($pvtResult);
        $this->db->doTransaction();
    }

    public function testThrowsUniqueConstraintViolationWhenAttemptingToInsertDuplicateRow()
    {
        $pvtResult = new PvtResult(10001, 1234567890, 5, array(1.1, 2.2, 3.3, 4.4, 5.5));
        $this->db->expects($this->at(0))
            ->method('insert')
            ->will($this->throwException(DBALException::driverExceptionDuringQuery(new \Exception('unique key violation', 23505), 'sql')));
        $this->setExpectedException('Pvt\Exceptions\UniqueConstraintViolationException');
        $this->store->save($pvtResult);
        $this->db->doTransaction();
    }

    public function testRethrowsOtherDatabaseErrors()
    {
        $pvtResult = new PvtResult(10001, 1234567890, 5, array(1.1, 2.2, 3.3, 4.4, 5.5));
        $this->db->expects($this->at(0))
            ->method('insert')
            ->will($this->throwException(new DBALException()));
        $this->setExpectedException('Doctrine\DBAL\DBALException');
        $this->store->save($pvtResult);
        $this->db->doTransaction();
    }

    public function testInsertsIndividualResponseTimes()
    {
        $pvtResult = new PvtResult(10001, 1234567890, 5, array(1.1, 2.2, 3.3, 4.4, 5.5));
        $this->db->expects($this->at(1))
            ->method('insert')
            ->with(
                'pvt_results_response_times',
                array(
                    'user_id' => 10001,
                    'timestamp' => 1234567890,
                    'sequence' => 1,
                    'response_time' => 1.1
                )
            );
        $this->db->expects($this->at(2))
            ->method('insert')
            ->with(
                'pvt_results_response_times',
                array(
                    'user_id' => 10001,
                    'timestamp' => 1234567890,
                    'sequence' => 2,
                    'response_time' => 2.2
                )
            );
        $this->db->expects($this->at(3))
            ->method('insert')
            ->with(
                'pvt_results_response_times',
                array(
                    'user_id' => 10001,
                    'timestamp' => 1234567890,
                    'sequence' => 3,
                    'response_time' => 3.3
                )
            );
        $this->db->expects($this->at(4))
            ->method('insert')
            ->with(
                'pvt_results_response_times',
                array(
                    'user_id' => 10001,
                    'timestamp' => 1234567890,
                    'sequence' => 4,
                    'response_time' => 4.4
                )
            );
        $this->db->expects($this->at(5))
            ->method('insert')
            ->with(
                'pvt_results_response_times',
                array(
                    'user_id' => 10001,
                    'timestamp' => 1234567890,
                    'sequence' => 5,
                    'response_time' => 5.5
                )
            );
        $this->store->save($pvtResult);
        $this->db->doTransaction();
    }
}
