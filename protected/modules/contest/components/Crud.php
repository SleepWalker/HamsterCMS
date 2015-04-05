<?php

namespace contest\components\crud;

abstract class Crud extends \CComponent
{
    protected $formId;
    protected $_modelName;
    protected $_model;

    public function __construct()
    {
        $this->_modelName = \CHtml::modelName($this->crudModel);
        if (empty($this->formId)) {
            $this->formId = $this->modelName;
        }
    }

    abstract public function getCrudModel();

    protected function throwValidationError($model)
    {
        throw new \CException('Error saving model ' . get_class($model) . ': ' . PHP_EOL . var_export($model->errors, true));
    }

    public function save($data = array())
    {
        $this->model->attributes = $data;
        return $this->model->save();
    }

    public function saveByModelName($data = array())
    {
        // TODO: метод для сохранения только аттрибутов, а не POST массива
        if (!isset($data[$this->modelName])) {
            return false;
        }

        $transaction = \Yii::app()->db->beginTransaction();
        try {
            $this->model->attributes = $data;

            if (!$this->save($data[$this->modelName])) {
                $this->throwValidationError($this->model);
            }

            $this->saveRelations($data);

            $transaction->commit();

            return true;
        } catch (\CException $e) {
            $transaction->rollBack();
        }

        return false;
    }

    protected function saveRelations($data = array())
    {
    }

    public function newModel()
    {
        $this->_model = new $this->crudModel();

        return $this->_model;
    }

    public function loadModel($id)
    {
        $this->_model = call_user_func(array($this->crudModel, 'model'))->findByPk($id);

        return $this->_model;
    }

    public static function getCrud($id = false)
    {
        $crud = new static();

        if ($id) {
            $crud->loadModel($id);
        } else {
            $crud->newModel();
        }

        return $crud;
    }

    public function validateAjax()
    {
        if (isset($_POST['ajax']) && $_POST['ajax'] == $this->formId) {
            echo \CActiveForm::validate($this->model);

            \Yii::app()->end();
        }
    }

    public function getModel()
    {
        return $this->_model;
    }

    public function getModelName()
    {
        return $this->_modelName;
    }

    public function delete()
    {
        return $this->_model->delete();
    }

    public function search($data = array(), $scenario = 'search')
    {
        $this->_model->scenario = $scenario;
        $this->_model->unsetAttributes();
        if (isset($data[$this->modelName])) {
            $this->_model->attributes = $data[$this->modelName];
        }
    }

    // protected function saveRelations($data = array())
    // {
    //     foreach ($this->model->relations() as $relationName => $config) {
    //         $modelClass = $config[1];
    //         $modelName = \CHtml::modelName($modelClass);

    //         if (!isset($data[$modelName])) {
    //             continue;
    //         }

    //         $modelData = $data[$modelName];
    //         if ($config[0] === \CActiveRecord::HAS_MANY) {
    //             $relationAttribute = $config[2];

    //             call_user_func(array($modelClass, 'model'))->deleteAllByAttributes(array(
    //                 $relationAttribute => $this->model->primaryKey,
    //                 ));

    //             foreach ($modelData as $index => $attributes) {
    //                 $model = new $modelClass();
    //                 $model->attributes = $attributes;
    //                 $model->$relationAttribute = $this->model->primaryKey;

    //                 if (!$model->save()) {
    //                     $this->throwValidationError($model);
    //                 }
    //             }

    //             // reset relation models
    //             unset($this->model->$relationName);
    //         }
    //     }
    // }
}
