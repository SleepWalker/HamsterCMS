<?php
/**
 * An interface for models, that are aggregates of other models
 */
namespace hamster\models;

interface Aggregate
{
    public function getModels() : array;
}
