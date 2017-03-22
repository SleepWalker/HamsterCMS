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

use KoKoKo\assert\Assert;

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
        return [
            ['author, title, duration', 'required', 'message' => false],
            ['duration', 'numerical', 'integerOnly' => true, 'max' => 15, 'message' => 'Тривалість повинна бути цілим числом'],
            ['author, title', 'length', 'max' => 128],

            ['request_id', 'length', 'max' => 11],
        ];
    }

    protected function beforeSave()
    {
        Assert::assert($this->request_id, 'request_id')->numeric();

        return parent::beforeSave();
    }

    public function attributeLabels()
    {
        return [
            'author' => 'Виконавець/Назва гурту',
            'title' => 'Назва композиції',
            'duration' => 'Тривалість, хв',
        ];
    }

    public function getFullName()
    {
        return $this->author . ' — ' . $this->title;
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return [
            'request' => [self::BELONGS_TO, Request::class, 'request_id'],
        ];
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
