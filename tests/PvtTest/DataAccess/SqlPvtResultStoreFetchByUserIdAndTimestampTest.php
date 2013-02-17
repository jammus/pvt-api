<?php

namespace PvtTest\DataAccess;

use Pvt\DataAccess\SqlPvtResultStore;

class SqlPvtResultStoreFetchByUserIdAndTimestampTest extends \PvtTest\PvtDatabaseTestCase
{
    private $store;

    public function setup()
    {
        parent::setup();
        $this->store = new SqlPvtResultStore($this->db);
    }

    public function testJoinsResultAndResponseTimeTables()
    {
        $this->db->expects($this->once())
            ->method('fetchAll')
            ->with(
                'SELECT t1.*, t2.response_time FROM pvt_results AS t1, pvt_results_response_times AS t2 WHERE t1.user_id = t2.user_id AND t1.timestamp = t2.timestamp AND t1.user_id = :user_id AND t1.timestamp = :timestamp ORDER BY t2.sequence',
                $this->anything()
            );
        $this->store->fetchByUserIdAndTimestamp(10001, 1234567890);
    }

    public function testRestrictsResultByUserIdAndTimestamp()
    {
        $this->db->expects($this->once())
            ->method('fetchAll')
            ->with(
                $this->anything(),
                array(
                    'user_id' => 10001,
                    'timestamp' => 1234567890
                )
            );
        $this->store->fetchByUserIdAndTimestamp(10001, 1234567890);
    }

    public function testReturnsPvtResultObjectWhenFound()
    {
        $this->db->expects($this->any())
            ->method('fetchAll')
            ->will($this->returnValue(
                array(
                    array(
                        'user_id' => 10001,
                        'timestamp' => 1234567890,
                        'average_response_time' => 123,
                        'error_count' => 2,
                        'response_time' => 1.1
                    ),
                    array(
                        'user_id' => 10001,
                        'timestamp' => 1234567890,
                        'average_response_time' => 123,
                        'error_count' => 2,
                        'response_time' => 2.2
                    ),
                    array(
                        'user_id' => 10001,
                        'timestamp' => 1234567890,
                        'average_response_time' => 123,
                        'error_count' => 2,
                        'response_time' => 3.3
                    ),
                    array(
                        'user_id' => 10001,
                        'timestamp' => 1234567890,
                        'average_response_time' => 123,
                        'error_count' => 2,
                        'response_time' => 4.4
                    ),
                    array(
                        'user_id' => 10001,
                        'timestamp' => 1234567890,
                        'average_response_time' => 123,
                        'error_count' => 2,
                        'response_time' => 5.5
                    ),
                )
            ));

        $pvtResult = $this->store->fetchByUserIdAndTimestamp(10001, 1234567890);

        $this->assertEquals(10001, $pvtResult->userId());
        $this->assertEquals(\DateTime::createFromFormat('U', 1234567890), $pvtResult->date());
        $this->assertEquals(2, $pvtResult->errors());
        $this->assertEquals(3.3, $pvtResult->averageResponseTime());
    }

    public function testReturnsNullIsNoRowsFound()
    {
        $this->db->expects($this->any())
            ->method('fetchAll')
            ->will($this->returnValue(array()));
        $pvtResult = $this->store->fetchByUserIdAndTimestamp(10001, 1234567890);
    }
}
