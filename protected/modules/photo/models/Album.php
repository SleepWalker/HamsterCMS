<?php

/**
 * This is the model class for table "album".
 *
 * The followings are the available columns in table 'album':
 * @property string $id
 * @property string $name
 * @property string $desc
 * @property string $photo_id
 *
 * The followings are the available model relations:
 * @property Photo $photo
 * @property Photo[] $photos
 */
class Album extends CActiveRecord
{
  private static $albums;

  public static function all(){
    if(!self::$albums)
      self::$albums = Album::model()->findAll();
    return self::$albums;
  }


	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Album the static model class
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
		return 'photo_album';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, desc', 'required'),
			array('name', 'length', 'max'=>64),
			array('photo_id', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, desc, photo_id', 'safe', 'on'=>'search'),
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
			'photo' => array(self::BELONGS_TO, 'Photo', 'photo_id'),
			'photos' => array(self::HAS_MANY, 'Photo', 'album_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Название альбома',
			'desc' => 'Описание',
			'photo_id' => 'Случайная фотография',
		);
	}
  
  /**
	 * @return array типы полей для форм администрирования модуля
	 */
	public function getFieldTypes()
	{
		return array(
			'name' => 'text',
			'desc' => 'textarea',
			//'photo_id' => 'translit',
		);
	}
  
  /**
	 * @return array для использования в dropDownList
	 */
	public function getAlbumsList()
	{
	  $list = $this->findAll(array('select'=>'id, name', 'order' => 'name'));
	  foreach($list as $album)
	  {
	    $ddList[$album->id] = $album->name;
	  }
	  
	  return $ddList;
	}
  
  public function getViewUrl()
  {
    return Yii::app()->createUrl('photo/photo/album', array('id' => $this->primaryKey));
  }

  /**
   * getAlbumsMenu возвращает массив меню альбомов для CMenu виджета.
   * 
   * @access public
   * @return array Меню альбомов
   */
  public function getAlbumsMenu()
  {
    $albums = $this->findAll();
    foreach($albums as $album)
    {
      $menu[] = array(
        'label' => $album->name,
        'url' => $album->viewUrl,
      );
    }
    return $menu;
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
		$criteria->compare('desc',$this->desc,true);
		$criteria->compare('photo_id',$this->photo_id,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}
