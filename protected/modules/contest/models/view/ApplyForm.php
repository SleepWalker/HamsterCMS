<?php
/**
 * An aggregate over Request, Muscian and Composition
 */
namespace contest\models\view;

use contest\models\Request;
use contest\models\Musician;
use contest\models\Composition;

class ApplyForm extends \CFormModel
{
    private $_request;
    private $_musicians = [];
    private $_compositions = [];

    const MAX_COMPOSITIONS = 2;
    const MAX_MUSICIANS = 7;

    public function __construct()
    {
        $this->init();
        $this->attachBehaviors($this->behaviors());
        $this->afterConstruct();
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['compositions, musicians, request', 'required'],

            ['musicians', 'musiciansValidator'],
            ['compositions', 'compositionsValidator'],
            ['request', 'requestValidator'],
        ];
    }

    public function requestValidator($attribute, $params = [])
    {
        if (!$this->request->validate()) {
            return $this->addError('request', 'Часть полей формы были заполнены не верно');
        }
    }

    public function musiciansValidator($attribute, $params = [])
    {
        $hasContacts = false;
        $validCount = 0;
        foreach ($this->musicians as $musician) {
            if (!empty($musician->email)) {
                $hasContacts = true;
            }

            // валидируем только тем модели, которые заполнялись юзером
            if (!$musician->isEmpty() && $musician->validate()) {
                $validCount++;
            }
        }

        if ($this->getScenario() == 'group' && $validCount < 2) {
            return $this->addError('musicians', 'В группе должно быть хотя бы два участника');
        } elseif ($validCount === 0) {
            return $this->addError('musicians', 'Укажите информацию хотя бы об одном музыканте');
        }

        if (!$hasContacts) {
            return $this->addError('musicians', 'Пожалуйста, укажите email хоть одного музыканта');
        }
    }

    public function compositionsValidator($attribute, $params = [])
    {
        $valid = true;
        foreach ($this->compositions as $composition) {
            $valid = $composition->validate() && $valid;
        }

        if (!$valid) {
            $this->addError('compositions', 'Пожалуйста, укажите обе композиции');
        }
    }

    public function setScenario($value)
    {
        foreach ($this->getModels() as $model) {
            $model->setScenario($value);
        }
        parent::setScenario($value);
    }

    public function getModels()
    {
        return array_merge($this->musicians, $this->compositions, [$this->request]);
    }

    /**
     * @return array list for radio button/dropdown list
     */
    public function getFormatsList()
    {
        return $this->request->getFormatsList();
    }

    /**
     * Returns a value indicating whether there is any validation error.
     * @param string $attribute attribute name. Use null to check all attributes.
     * @return boolean whether there is any error.
     */
    public function hasErrors($attribute = null)
    {
        if (!$attribute && $this->childrenHasErrors()) {
            return true;
        }

        return parent::hasErrors($attribute);
    }

    /**
     * Проверяет есть ли ошибки в под моделях этой модели
     * @return boolean
     */
    private function childrenHasErrors()
    {
        $children = $this->getModels();

        foreach ($children as $child) {
            if ($child->hasErrors()) {
                return true;
            }
        }

        return false;
    }

    public function attributeLabels()
    {
        return [
            'compositions' => 'Исполняемые композиции',
            'musicians' => 'Исполнитель(-ли)',
        ];
    }

    public function getRequest()
    {
        if (empty($this->_request)) {
            $this->_request = new Request();
        }

        return $this->_request;
    }

    public function getMusicians()
    {
        if (empty($this->_musicians)) {
            $musicians = [];
            for ($i = 0; $i < self::MAX_MUSICIANS; $i++) {
                $musicians[] = new Musician();
            }
            $this->_musicians = $musicians;
        }

        return $this->_musicians;
    }

    public function getCompositions()
    {
        if (empty($this->_compositions)) {
            $compositions = [];
            for ($i = 0; $i < self::MAX_COMPOSITIONS; $i++) {
                $compositions[] = new Composition();
            }
            $this->_compositions = $compositions;
        }

        return $this->_compositions;
    }

    public function addMusician(array $attributes)
    {
        $model = new Musician();

        $model->setAttributes($attributes, false);
        array_push($this->_musicians, $model);
    }

    public function addComposition(array $attributes)
    {
        $model = new Composition();

        $model->setAttributes($attributes, false);
        array_push($this->_compositions, $model);
    }
}