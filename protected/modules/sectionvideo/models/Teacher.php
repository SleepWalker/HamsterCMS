<?php

/**
 * This is the model class for table "section_teacher".
 *
 * The followings are the available columns in table 'section_teacher':
 * @property string $id
 * @property string $school_id
 * @property string $first_name
 * @property string $last_name
 * @property string $middle_name
 * @property string $bio
 * @property string $photo
 * @property string $classes
 *
 * The followings are the available model relations:
 * @property SectionSchool $school
 * @property SectionVideoMusicians[] $sectionVideoMusicians
 */

namespace hamster\modules\sectionvideo\models;

use \Yii;
use \CDbCriteria;
use \CActiveDataProvider;
use \CHtml;

class Teacher extends \CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'section_teacher';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('last_name', 'required'),
			array('first_name', 'required', 'except' => 'simple'),
			array('school_id', 'length', 'max'=>10),
			array('first_name, last_name, middle_name', 'length', 'max'=>128),
			array('classes', 'length', 'max'=>45),
			array('bio, photo', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, school_id, first_name, last_name, middle_name, bio, photo, classes', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'school' => array(self::BELONGS_TO, 'SectionSchool', 'school_id'),
			'sectionVideoMusicians' => array(self::HAS_MANY, 'SectionVideoMusicians', 'teacher_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'school_id' => 'School',
			'first_name' => 'Имя',
			'last_name' => 'Фамилия',
			'middle_name' => 'Отчество',
			'bio' => 'Биография',
			'photo' => 'Фото',
			'classes' => 'Преподоваемые классы',
		);
	}

	public function getFullName()
	{
		$name = $this->last_name . ' ' . $this->first_name . ' ' . $this->middle_name;
		return trim(str_replace('  ', '', $name));
	}

	/**
	 * @return короткое имя преподователя Фамили Имя Отчество -> Фамилия И. О.
	 */
	public function getShortName()
	{
		$parts = explode(' ', $this->fullName);
		$parts[0] = $parts[0].' ';
		if(isset($parts[1]))
			$parts[1] = mb_substr($parts[1], 0, 1, 'UTF-8') . '.';
		if(isset($parts[2]))
			$parts[2] = mb_substr($parts[2], 0, 1, 'UTF-8') . '.';

		return implode('', $parts);
	}

	public function getViewUrl()
	{
		return Yii::app()->createUrl('page/view', array('id' => 'teacher')) . '#' . $this->shortName;
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('school_id',$this->school_id,true);
		$criteria->compare('first_name',$this->first_name,true);
		$criteria->compare('last_name',$this->last_name,true);
		$criteria->compare('middle_name',$this->middle_name,true);
		$criteria->compare('bio',$this->bio,true);
		$criteria->compare('photo',$this->photo,true);
		$criteria->compare('classes',$this->classes,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Teacher the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
