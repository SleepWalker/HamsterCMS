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
    public $format = 0;
    public $name;
    public $demos;

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('format, name, compositions, musicians', 'required'),
            array('name', 'length', 'max' => 64),
            array('type', 'in', 'range' => array('solo', 'group')),
            array('demos, format', 'safe'),
        );
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
        $musician = new \contest\models\view\Musician();
        $musicians = [$musician, $musician, $musician, $musician, $musician, $musician, $musician];
        return $musicians;
    }

    public function getCompositions()
    {
        $composition = new \contest\models\view\Composition();
        $compositions = [$composition, $composition];
        return $compositions;
    }
}
