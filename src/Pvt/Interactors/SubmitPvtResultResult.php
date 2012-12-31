<?php

namespace Pvt\Interactors;

use Pvt\Core\PvtResult;

class SubmitPvtResultResult extends Result
{
    const DUPLICATE_SUBMISSION = -1;

    private $pvtResult;

    public function __construct(PvtResult $pvtResult = null, Array $errors = array())
    {
        parent::__construct($errors);
        $this->pvtResult = $pvtResult;
    }

    public function pvtResult()
    {
        return $this->pvtResult;
    }
}
