<?php

/**
 * This is the model class for table "photo".
 *
 * The followings are the available columns in table 'photo':
 * @property string $id
 * @property string $name
 * @property string $desc
 * @property string $photo
 * @property string $album_id
 * @property string $date
 *
 * The followings are the available model relations:
 * @property Album[] $albums
 * @property Album $album
 */
class Photo extends CActiveRecord
{
  private static $album = array();
  public static function all($album_id){
    if(!isset(self::$album[$id])){
      $criteria=new CDbCriteria();
      $criteria->compare('album_id',$album_id,true);
      self::$album[$id] = Photo::model()->FindAll($criteria);
    }
    return self::$album[$id];
  }
  
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Photo the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}


	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'photo';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, album_id', 'required'),
			array('name, photo', 'length', 'max'=>64),
			array('album_id', 'length', 'max'=>10),
      array('desc', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, album_id, date', 'safe', 'on'=>'search'),
      array('date','default',
              'value'=>new CDbExpression('NOW()'),
              'setOnEmpty'=>false,'on'=>'insert'),
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
			'albums' => array(self::HAS_MANY, 'Album', 'photo_id'),
			'album' => array(self::BELONGS_TO, 'Album', 'album_id'),
		);
	}

  public function behaviors()
  {
    return array(
      'HIU'=>array(
        'class'=>'HIUBehavior',
        'fileAtt' => 'photo',
        'dirName' => 'photo',
        'forceExt' => 'jpg',
        'sizes'=>array(
          'normal' => array(
            'width'=>625,
          ),
          'full' => array(
            'width'=>1024,
          ),
          'thumb' => array(
            'width' => 150,
            'height' => 150,
            'crop' => true,
          ),
        ),
      ),
    );
  }

  public function defaultScope()
  {
    return array(
      'order'=>'date DESC',
    );
  }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Название фото',
			'desc' => 'Описание',
			'photo' => 'Фото',
      'uImage' => 'Фото',
			'album_id' => 'Альбом',
      'album' => 'Альбом',
			'date' => 'Дата',
		);
	}
  
  /**
	 * @return array типы полей для форм администрирования модуля
	 */
	public function getFieldTypes()
	{
		return array(
			'name' => 'text',
      'uImage' => 'file',
      'album_id' => array(
			  'dropdownlist',
			  'items' => Album::model()->albumsList,
			  'empty' => '--Выберите альбом--',
			),
			'desc' => 'textarea',
		);
	}
  
  /**
	 * Устанавливаем заглавную картинку в аьбоме
	 */
	protected function afterSave()
	{
	  parent::afterSave();
    if(!empty($this->photo))
    {
      $this->album->photo_id = $this->id;
      $this->album->save();
    }
	}
  
  public function getViewUrl()
  {
    return Yii::app()->createUrl('photo/photo/view', array('id' => $this->primaryKey));
  }
  
	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('album_id',$this->album_id,true);
		$criteria->compare('date',$this->date,true);
    $citeria->width = array('album');

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}
