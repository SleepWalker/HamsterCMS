<?php

namespace contest\models;

use KoKoKo\assert\Assert;

class ContestId
{
    public function __construct($value)
    {
        Assert::assert($value, 'value')->int()->inArray([1, 2]);

        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}
