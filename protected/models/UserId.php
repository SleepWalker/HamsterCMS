<?php
namespace hamster\models;

class UserId
{
    public function __contruct(string $userId)
    {
        if (!strlen($userId)) {
            throw new \InvalidArgumentException('Wrong user id');
        }
    }
}
