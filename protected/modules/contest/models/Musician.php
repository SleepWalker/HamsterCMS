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
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{contest_musician}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('request_id, first_name, last_name, birthdate, instrument', 'required'),
            array('request_id', 'length', 'max' => 11),
            array('first_name, last_name, school, class, teacher', 'length', 'max' => 128),
            array('email, instrument', 'length', 'max' => 64),
            array('phone', 'length', 'max' => 25),
        );
    }

    protected function beforeSave()
    {
        $this->birthdate = date('Y-m-d', strtotime($this->birthdate));

        return parent::beforeSave();
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
