<?php
/**
 * Is thrown, when model(s) validation was failed due to bad user input
 */

namespace hamster\components\exceptions;

use Exception;
use CModel;
use hamster\models\Aggregate;

class InvalidUserInputException extends Exception
{
    /**
     * @var CModel $model
     */
    protected $model;

    public function __construct(CModel $model, $code = 0, Exception $previous = null)
    {
        $this->model = $model;

        parent::__construct('User provided invalid data', $code, $previous);
    }

    public function getModel() : CModel
    {
        return $this->model;
    }

    /**
     * @return string json response with validation errors for ajax requests sent from yii active form
     */
    public function toActiveFormResponse() : string
    {
        return \CActiveForm::validate($this->getModels(), null, false);
    }

    /**
     * @return CModel[] an array of models affected in request
     */
    private function getModels() : array
    {
        if ($this->model instanceof Aggregate) {
            return $this->model->getModels();
        }

        return [$this->model];
    }
}
