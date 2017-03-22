<?php

namespace contest\models;

use KoKoKo\assert\Assert;

class ContestId
{
    const IS_CONTEST_ACTIVE = false;
    const IS_CONTEST = false; // whether this is a contest or a fest
    const CONTEST_ID = 3; // TODO: remove hardcode, when we will be able to create contest

    public function __construct(int $value)
    {
        Assert::assert($value, 'value')->int()->inArray([1, 2, 3]);

        $this->value = $value;
    }

    public function getValue() : int
    {
        return $this->value;
    }

    /**
     * The list of available contests
     * TODO: should be removed, when we will store contests in db
     */
    public static function getAll() : array
    {
        return [
            [
                'id' => 1,
                'title' => 'Рок єднає нас 2015',
            ],
            [
                'id' => 2,
                'title' => 'Рок єднає нас 2016',
            ],
            [
                'id' => 3,
                'title' => 'Рок єднає нас 2017',
            ],
        ];
    }
}
