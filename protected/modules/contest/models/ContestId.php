<?php

namespace contest\models;

use KoKoKo\assert\Assert;

class ContestId
{
    public function __construct(int $value)
    {
        Assert::assert($value, 'value')->int()->positive();

        $this->value = $value;
    }

    public function getValue() : int
    {
        return $this->value;
    }
}
