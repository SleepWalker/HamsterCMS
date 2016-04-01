<?php

namespace contest\components;

use contest\crud\RequestCrud;
use contest\models\ContestId;

class RequestRepository
{
    /**
     * @param  ContestId|null $contestId
     *
     * @return Request[]
     */
    public function findNotConfirmed(ContestId $contestId = null)
    {
        return RequestCrud::findNotConfirmed($contestId);
    }
}
