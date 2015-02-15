<?php

/**
 * This is the model class for table "section_video_musicians".
 *
 * The followings are the available columns in table 'section_video_musicians':
 * @property string $id
 * @property string $video_id
 * @property string $musician_id
 * @property string $instrument_id
 * @property string $teacher_id
 * @property integer $class
 * @property integer $sort_order
 *
 * The followings are the available model relations:
 * @property SectionVideo $video
 * @property SectionInstrument $instrument
 * @property SectionMusician $musician
 * @property SectionTeacher $teacher
 */

namespace hamster\modules\sectionvideo\models;

use \Yii;
use \CDbCriteria;
use \CDbExpression;
use \CActiveDataProvider;
use \CHtml;
use \CJavaScriptExpression;

class VideoMusicians extends \CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'section_video_musicians';
	}

	public function defaultScope()
	{
		$alias = $this->getTableAlias(true, false);
		return array(
			'order' => $alias.'.sort_order ASC',
		);
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('video_id, musician_id', 'required'),
			array('class', 'numerical', 'integerOnly'=>true),
			array('video_id, musician_id, instrument_id, teacher_id', 'length', 'max'=>10),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, video_id, musician_id, instrument_id, teacher_id, class', 'safe', 'on'=>'search'),
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
			'video' => array(self::BELONGS_TO, '\hamster\modules\sectionvideo\models\Video', 'video_id'),
			'instrument' => array(self::BELONGS_TO, '\hamster\modules\sectionvideo\models\Instrument', 'instrument_id'),
			'musician' => array(self::BELONGS_TO, '\hamster\modules\sectionvideo\models\Musician', 'musician_id'),
			'teacher' => array(self::BELONGS_TO, '\hamster\modules\sectionvideo\models\Teacher', 'teacher_id'),
		);
	}

	protected function beforeSave()
	{
		$this->teacher_id = empty($this->teacher_id) ? new CDbExpression('NULL') : $this->teacher_id;
		$this->instrument_id = empty($this->instrument_id) ? new CDbExpression('NULL') : $this->instrument_id;

		return parent::beforeSave();
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'video_id' => 'Video',
			'musician_id' => 'Musician',
			'instrument_id' => 'Instrument',
			'teacher_id' => 'Teacher',
			'class' => 'Класс ученика',
		);
	}

	/**
	 * @return array типы полей для форм администрирования модуля
	 */
	public function getFieldTypes()
	{
		return array(
			'id' => 'hidden',
			'musician_id' => array(
				'type' => 'ext.fields.jui.HRelationAutoComplete',
				'relation' => 'musician',
				'relationAttribute' => 'name',
				),
			'instrument_id' => array(
				'type' => 'ext.fields.jui.HRelationAutoComplete',
				'relation' => 'instrument',
				'relationAttribute' => 'name',
				),
			'class' => 'text',
			'teacher_id' => array(
				'type' => 'ext.fields.jui.HRelationAutoComplete',
				'relation' => 'teacher',
				'searchAttributes' => 'first_name, last_name, middle_name',
				'relationAttribute' => 'fullName',
				),
			);
	}

	public function getName()
	{
		return isset($this->musician) ? $this->musician->name : '';
	}

	public function getClass()
	{
		return isset($this->musician) ? $this->musician->class : '';
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
		$criteria->compare('video_id',$this->video_id,true);
		$criteria->compare('musician_id',$this->musician_id,true);
		$criteria->compare('instrument_id',$this->instrument_id,true);
		$criteria->compare('teacher_id',$this->teacher_id,true);
		$criteria->compare('class',$this->class);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return VideoMusicians the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
