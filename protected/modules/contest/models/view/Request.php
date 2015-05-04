<?php
/**
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @copyright  Copyright &copy; 2015 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

namespace contest\models\view;

class Request extends \CFormModel
{
    public $type = 'solo';
    public $format = self::FORMAT_SOLO;
    public $name;
    public $demos;

    private $_musicians;
    private $_compositions;

    const MAX_COMPOSITIONS = 2;
    const MAX_MUSICIANS = 7;

    const FORMAT_SOLO = 1;
    const FORMAT_MINUS = 2;
    const FORMAT_CONCERTMASTER = 3;

    const TYPE_GROUP = 'group';
    const TYPE_SOLO = 'solo';

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('compositions, musicians', 'required'),
            array('name', 'required', 'except' => 'solo'),
            array('format', 'required', 'except' => 'group'),
            array('name', 'length', 'max' => 64),
            array('type', 'in', 'range' => array(self::TYPE_SOLO, self::TYPE_GROUP)),
            array('demos', 'safe'),
            array('format', 'numerical', 'integerOnly' => true),

            array('musicians', 'musiciansValidator'),
            array('compositions', 'compositionsValidator'),
        );
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
        foreach (array_merge($this->musicians, $this->compositions) as $model) {
            $model->setScenario($value);
        }
        parent::setScenario($value);
    }

    /**
     * @return array list for radio button/dropdown list
     */
    public function getFormatsList()
    {
        return [
            self::FORMAT_SOLO => 'Сольное исполнение (без сопровождения)',
            self::FORMAT_MINUS => 'Сольное исполнение под минус',
            self::FORMAT_CONCERTMASTER => 'Сольное исполнение с концертмейстером',
        ];
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
        $children = array_merge($this->musicians, $this->compositions);

        foreach ($children as $child) {
            if ($child->hasErrors()) {
                return true;
            }
        }

        return false;
    }

    public function attributeLabels()
    {
        return array(
            'name' => 'Название группы',
            'demos' => 'Ссылки на демо записи',
            'type' => 'Номинация',
            'format' => 'Формат номера',
            'compositions' => 'Исполняемые композиции',
            'musicians' => 'Исполнитель(-ли)',
        );
    }

    public function getMusicians()
    {
        if (!isset($this->_musicians)) {
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
        if (!isset($this->_compositions)) {
            $compositions = [];
            for ($i = 0; $i < self::MAX_COMPOSITIONS; $i++) {
                $compositions[] = new Composition();
            }
            $this->_compositions = $compositions;
        }

        return $this->_compositions;
    }
}
