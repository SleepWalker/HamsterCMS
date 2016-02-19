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
    public $contacts = 'this is for label only';
    public $nomination = 'this is for label only';

    private $_request;
    private $_musicians = [];
    private $_compositions = [];

    const MAX_COMPOSITIONS = 2;
    const MAX_MUSICIANS = 7;

    public function __construct(Request $request = null, array $musicians = [], array $compositions = [])
    {
        if ($request) {
            array_walk($musicians, function (Musician $musician) {
            });
            array_walk($compositions, function (Composition $composition) {
            });
            $this->_request = $request;
            $this->_musicians = $musicians;
            $this->_compositions = $compositions;
        }

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
            ['compositions, musicians, request, contacts, nomination', 'required'],

            ['musicians', 'musiciansValidator'],
            ['compositions', 'compositionsValidator'],
            ['request', 'requestValidator'],
        ];
    }

    public function requestValidator($attribute, $params = [])
    {
        if (!$this->request->validate()) {
            return $this->addError('request', 'Будь ласка, заповніть усі обовь\'язкові поля форми');
        }
    }

    public function musiciansValidator($attribute, $params = [])
    {
        $validCount = 0;
        foreach ($this->musicians as $musician) {
            // валидируем только тем модели, которые заполнялись юзером
            if (!$musician->isEmpty() && $musician->validate()) {
                $validCount++;
            }
        }

        if ($this->getScenario() === Request::SCENARIO_GROUP && $validCount < 2) {
            return $this->addError('musicians', 'В гурті повинно бути хоча б два виконавця');
        } elseif ($validCount === 0) {
            return $this->addError('musicians', 'Вкажіть інформацію хоча про одного виконавця');
        }
    }

    public function compositionsValidator($attribute, $params = [])
    {
        $valid = true;
        foreach ($this->compositions as $composition) {
            $valid = $composition->validate() && $valid;
        }

        if (!$valid) {
            $this->addError('compositions', 'Будь ласка, вкажіть обидві композиції');
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

    public function load(\CHttpRequest $request)
    {
        $requestData = $request->getPost(\CHtml::modelName(Request::class), []);
        $compositionsData = $request->getPost(\CHtml::modelName(Composition::class), []);
        $musiciansData = $request->getPost(\CHtml::modelName(Musician::class), []);

        $this->request->attributes = $requestData;

        foreach ($this->compositions as $index => $composition) {
            if (isset($compositionsData[$index])) {
                $composition->attributes = $compositionsData[$index];
            }
        }

        foreach ($this->musicians as $index => $musician) {
            if (isset($musiciansData[$index])) {
                $musician->attributes = $musiciansData[$index];
            }
        }

        $this->setScenario(
            $this->request->isGroup()
            ? Request::SCENARIO_GROUP
            : Request::SCENARIO_SOLO
        );
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
            'compositions' => 'Виконувані композиції',
            'musicians' => 'Виконавець(-вці)',
            'contacts' => 'Контакти',
            'nomination' => 'Номінація',
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
}
