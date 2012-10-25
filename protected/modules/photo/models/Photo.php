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
  public static $uploadsUrl = '/uploads/photo/';
  
  private static $album = array();
  public static function all($album_id){
    if(!isset(self::$album[$id])){
      $criteria=new CDbCriteria();
      $criteria->compare('album_id',$album_id,true);
      self::$album[$id] = Photo::model()->FindAll($criteria);
    }
    return self::$album[$id];
  }
  
  public $uImage; // поле для загрузки фото
  
  public $quality = array(
    'png' => 7,
    'jpg' => 75,
    'gif' => 256,
  );
  
  public $sizes = array(
    'normal' => array(
      'width'=>625,
    ),
    'full' => array(
      'width'=>1024,
      'prefix' => 'full/',
    ),
    'thumb' => array(
      'width' => 150,
      'height' => 150,
      'prefix' => 'thumb/',
    ),
  );
  
  public function generate_url($type){
    return '/_upload/'.$this->photo;
  }
  
  public function preview_url(){
    return $this->generate_url('preview');
  }
  
  public function big_url(){
    return $this->generate_url('big');
  }
  
  public function full_url(){
    return $this->generate_url('full');
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
      array('uImage', 'file',
        'types'=>'jpg, gif, png',
        'maxSize'=>1024 * 1024 * 5, // 5 MB
        'allowEmpty'=>'true',
        'maxFiles' => 1,
        'tooLarge'=>'Файл весит больше 5 MB. Пожалуйста, загрузите файл меньшего размера.',
        'safe' => true,
			),
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
	 * Сохраняем загруженное изображение и заполняем модель оставшимися данными
	 */
	protected function beforeSave()
	{
	  if(parent::beforeSave())
    {
      Yii::import('admin.models.Image', true);
      Image::processUpload($this, 'photo', 'jpg');
      return true;
    }
    else
      return false;
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
  
  /**
	 * Удаляем картинки, загруженные в альбом
	 */
	protected function afterDelete()
	{
	  parent::afterDelete();
    foreach($this->sizes as $sizeName => $size)
    {
      $file = $this->uploadPath.$size['prefix'].$this->photo;
      if(file_exists($file))
        unset($file); // удаляем картинку
    }
	}
  
  /**
   *  @return путь к папке для загрузки файлов
   */
  public function getUploadPath()
  {
    $dir = Yii::getPathOfAlias('webroot') . self::$uploadsUrl;
    foreach($this->sizes as $sizeName => $size)
      if(!is_dir($dir.$size['prefix']))
        mkdir($dir.$size['prefix']); // создаем директорию для картинок
    return $dir;
  }
  // FIXME это создавалось чисто для вьюхи update... надо бы, что бы во всех местах использовался статический вариант этой переменной
  public function getUploadsUrl()
  {
    return self::$uploadsUrl;
  }
  
  public function img($code = 'normal')
  {
    if($this->photo)
      return CHtml::image(self::$uploadsUrl.$this->sizes[$code]['prefix'].$this->photo, $this->name, array('width'=>$this->sizes[$code]['width']));
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
