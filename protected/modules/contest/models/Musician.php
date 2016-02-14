<?php

/**
 * This is the model class for table "contest_musician".
 *
 * The followings are the available columns in table 'contest_musician':
 * @property string $id
 * @property string $request_id
 * @property string $first_name
 * @property string $last_name
 * @property string $birthdate
 * @property string $email
 * @property string $phone
 * @property string $instrument
 * @property string $school
 * @property string $class
 * @property string $teacher
 *
 * The followings are the available model relations:
 * @property ContestRequest $request
 */

namespace contest\models;

class Musician extends \CActiveRecord
{
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['request_id, first_name, last_name, instrument', 'required'],
            ['request_id', 'length', 'max' => 11],
            ['last_name, birthdate', 'required', 'except' => ['group']],

            ['first_name, last_name, school, teacher', 'length', 'max' => 128],
            ['email, instrument', 'length', 'max' => 64],

            ['phone', 'length', 'max' => 25],
            ['phone', 'match', 'pattern' => '/\+38 \(\d{3}\) \d{3}\-\d{2}\-\d{2}/'],

            ['birthdate', 'match', 'pattern' => '/\d{2}\.\d{2}\.\d{4}/'],

            ['email', 'email'],

            ['class', 'safe'],
        ];
    }

    public function isEmpty()
    {
        $empty = true;

        foreach ($this->attributeNames() as $attribute) {
            $empty = $empty && empty($this->$attribute);
        }

        return $empty;
    }

    public function getFullName()
    {
        return implode(' ', [
            $this->first_name,
            $this->last_name,
        ]);
    }

    public function getAge()
    {
        return (int)date('Y')-(int)date('Y', strtotime($this->birthdate));
    }

    protected function beforeSave()
    {
        if (preg_match('/\d{2}\.\d{2}\.\d{4}/', $this->birthdate)) {
            $this->birthdate = implode('-', array_reverse(explode('.', $this->birthdate)));
        } elseif (!preg_match('/\d{4}\-\d{2}\-\d{2}/', $this->birthdate)) {
            throw new \Exception('Unsupported date format: ' . $this->birthdate);
        }

        return parent::beforeSave();
    }

    protected function afterSave()
    {
        parent::afterSave();
        $this->birthdate = date('d.m.Y', strtotime($this->birthdate));
    }

    protected function afterFind()
    {
        parent::afterFind();
        $this->birthdate = date('d.m.Y', strtotime($this->birthdate));
    }

    public function attributeLabels()
    {
        return [
            'first_name' => 'Имя',
            'last_name' => 'Фамилия',
            'birthdate' => 'Дата рождения',
            'email' => 'Email',
            'phone' => 'Телефон',
            'instrument' => 'Инструмент/Вокал',
            'school' => 'Школа/коледж/училище',
            'teacher' => 'Преподаватель',
            'class' => 'Класс/курс',
        ];
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'request' => array(self::BELONGS_TO, '\contest\models\Request', 'request_id'),
        );
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{contest_musician}}';
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Musician the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
