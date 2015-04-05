<?php
/**
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @copyright  Copyright &copy; 2015 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

namespace contest\models\view;

class Musician extends \CFormModel
{
    public $first_name;
    public $last_name;
    public $birthdate;

    public $email;
    public $phone;

    public $instrument;
    public $school;
    public $class;
    public $teacher;

    public function isEmpty()
    {
        $empty = true;

        foreach ($this->attributeNames() as $attribute) {
            $empty = $empty && empty($this->$attribute);
        }

        return $empty;
    }

    public function rules()
    {
        return array(
            array('first_name, last_name, instrument', 'required'),
            array('last_name, birthdate', 'required', 'except' => array('group')),

            array('first_name, last_name, school, teacher', 'length', 'max' => 128),
            array('email, instrument', 'length', 'max' => 64),

            array('phone', 'length', 'max' => 25),
            array('phone', 'match', 'pattern' => '/\+38 \(\d{3}\) \d{3}\-\d{2}\-\d{2}/'),

            array('birthdate', 'match', 'pattern' => '/\d{2}\.\d{2}\.\d{4}/'),

            array('email', 'email'),

            array('class', 'safe'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'first_name' => 'Имя',
            'last_name' => 'Фамилия',
            'birthdate' => 'Дата рождения',
            'email' => 'Email',
            'phone' => 'Телефон',
            'instrument' => 'Инструмент/Вокал',
            'school' => 'Школа/коледж/училище',
            'teacher' => 'Преподаватель',
            'class' => 'Класс/курс',
        );
    }
}
