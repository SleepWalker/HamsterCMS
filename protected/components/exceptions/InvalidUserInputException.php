<?php
/**
 * Is thrown, when model(s) validation was failed due to bad user input
 */

namespace hamster\components\exceptions;

use Exception;

class InvalidUserInputException extends Exception
{
    /**
     * @var array $models
     */
    protected $models = [];

    public function __construct(array $models = [], $code = 0, Exception $previous = null)
    {
        $this->models = $models;

        parent::__construct('User provided invalid data', $code, $previous);
    }

    /**
     * @return CModel[] an array of models affected in request
     */
    public function getModels() : array
    {
        return $this->models;
    }

    /**
     * @return string response with validation errors for ajax requests sent from yii active form
     */
    public function toActiveFormResponse() : string
    {
        return \CActiveForm::validate($this->getModels(), null, false);
    }
}
