<?php

/**
 * This is the model class for table "contest_composition".
 *
 * The followings are the available columns in table 'contest_composition':
 * @property string $id
 * @property string $request_id
 * @property string $author
 * @property string $title
 * @property integer $duration
 *
 * The followings are the available model relations:
 * @property ContestRequest $request
 */

namespace contest\models;

class Composition extends \CActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{contest_composition}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('request_id, author, title, duration', 'required'),
            array('duration', 'numerical', 'integerOnly' => true),
            array('request_id', 'length', 'max' => 11),
            array('author, title', 'length', 'max' => 128),
        );
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
     * @return Composition the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
