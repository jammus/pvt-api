<?php

namespace Pvt\Core;

class PvtResult
{
    private $userId;

    private $timestamp;

    private $responses;

    /**
     * @param $userId int Id of user that performed the task
     * @param $timestamp int Timestamp in seconds
     */
    public function __construct($userId, $timestamp, Array $responses = array())
    {
        $this->userId = $userId;
        $this->timestamp = $timestamp;
        $this->responses = $responses;
    }

    /**
     * The id of the user that performed the task.
     *
     * @return int
     */
    public function userId()
    {
        return $this->userId;
    }

    /**
     * The DateTime that the task was undertaken.
     *
     * @return \DateTime
     */
    public function date()
    {
        return \DateTime::createFromFormat('U', $this->timestamp);
    }

    /**
     * The number of errors made during the task. A response
     * time of 500ms or greater is considered an error.
     *
     * @return int
     */
    public function errors()
    {
        return array_reduce(
            $this->responses,
            function ($count, $response) {
                return $response >= 500 ? $count + 1 : $count;
            },
            0
        );
    }

    /**
     * The individual response times reported.
     *
     * @return array[]float
     */
    public function responses()
    {
        return $this->responses;
    }

    /**
     * @return float|null Null if no times reported.
     */
    public function averageResponseTime()
    {
        if (empty($this->responses)) {
            return null;
        }
        return array_sum($this->responses) / count($this->responses);
    }

    /**
     * @return string Url where associated report can be obtained.
     */
    public function reportUrl()
    {
        return '/users/' . $this->userId . '/report/' . $this->timestamp;
    }
}
