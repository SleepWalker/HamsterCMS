<?php

namespace sectionvideo\components;

abstract class Repository extends \CApplicationComponent
{
    abstract protected function getModel();

    public function all()
    {
        return $this->getModel()->findAll();
    }

    public function save(\CActiveRecord $model)
    {
        if (!$model->save()) {
            throw new \Exception('Error saving model: ' . var_export($model->errors, true));
        }
    }
}
