<?php

namespace sectionvideo\components;

abstract class Repository extends \CApplicationComponent
{
    abstract protected function getModel();

    public function all()
    {
        return $this->getModel()->findAll();
    }

    public function get($id)
    {
        $this->assertId($id);

        $model = $this->getModel()->findByPk($id);

        if (!$model) {
            throw new \DomainException('The model does not exist');
        }

        return $model;
    }

    /**
     * @throws \DomainException if invalid model
     */
    public function save(\CActiveRecord $model)
    {
        if (!$model->save()) {
            throw new \DomainException('Error saving model: ' . var_export($model->errors, true));
        }
    }

    protected function assertId($videoId)
    {
        if (!is_numeric($videoId)) {
            throw new \DomainException('Wrong video id provided: ' . $videoId);
        }
    }
}
