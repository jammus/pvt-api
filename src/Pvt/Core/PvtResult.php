<?php

namespace Pvt\Core;

class PvtResult
{
    private $userId;

    private $timestamp;

    private $errors;

    private $responses;

    /**
     * @param $userId int Id of user that performed the task
     * @param $timestamp int Timestamp in seconds
     * @param $errors int Number of errors made
     */
    public function __construct($userId, $timestamp, $errors = 0, Array $responses = array())
    {
        $this->userId = $userId;
        $this->timestamp = $timestamp;
        $this->errors = $errors;
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
     * The number of errors made during the task.
     *
     * @return int
     */
    public function errors()
    {
        return $this->errors;
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
}
