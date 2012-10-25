<?php

/**
 * This is the model class for table "image".
 *
 * The followings are the available columns in table 'image':
 * @property string $name
 * @property string $source
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.ShopController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Image extends CActiveRecord
{
  // поле для загружаемого изображения (используется для обеспечения валидации
  public $uImage;
  
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
  
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Image the static model class
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
		return 'image';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, source, uImage', 'required'),
			array('name', 'length', 'max'=>40),
			array('source', 'length', 'max'=>256),
      array('uImage', 'file',
        'types'=>'jpg, gif, png',
        'maxSize'=>1024 * 1024 * 5, // 5 MB
        'maxFiles' => 1,
        'tooLarge'=>'Файл весит больше 5 MB. Пожалуйста, загрузите файл меньшего размера.',
        'safe' => true,
			),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('name, source', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'name' => 'Name',
			'source' => 'Uploaded From',
		);
	}
  
  /**
	 * Сохраняем загруженное изображение и заполняем модель оставшимися данными
	 */
	protected function beforeValidate()
	{
	  if(parent::beforeValidate())
    {
      if($this->isNewRecord)
      {
        //uri страницы, с которой происходила загрузка
        $this->source = str_replace($_SERVER['HTTP_HOST'], '',
          substr($_SERVER['HTTP_REFERER'], 7)
        );
        
        $this->name = self::processUpload($this, 'name');
      }
      return true;
    }
    else
      return false;
	}
  
  /**
   * Обрабатывает загруженную картинку и сохраняет ее название в $attribute
   * @param string $model модуль, в которую загружается картинка
   *    у модели должны быть поля: $uploadPath, $sizes, $quality
   *    так же у моели должно быть заполненно поле CUploadedFile $uImage
   * @param string $attribute имя атрибута в котором хранится название файла картинки
   * @param string $forceExt сохранять все картинки в это расширении
   */
  public static function processUpload($model, $attribute, $forceExt = false)
  {   
    if(!preg_match('%/$%', $model->uploadPath)) $uploadPath = $model->uploadPath.'/';
    else $uploadPath = $model->uploadPath;
    // @see http://www.php.net/manual/en/features.file-upload.errors.php
    if(!$model->uImage)
    {
      if($_POST[get_class($model)][$attribute] == 'delete')
        foreach($model->sizes as $sizeName => $size)
          if(file_exists($uploadPath.$size['prefix'].$model->{$attribute}))
            unlink($uploadPath.$size['prefix'].$model->{$attribute});
      return;
    }
      
    if($forceExt) $ext = $forceExt;
    else{
      $ext = $model->uImage->extensionName;
      if($ext == 'jpeg') $ext = 'jpg';
    }
    
    // setting file's mysterious name
    $fileName = uniqid().'.'.$ext;
    
    Yii::import('application.vendors.wideImage.WideImage');
    $wideImage = WideImage::load($model->uImage->tempName);
    $initialWidth = $wideImage->getWidth();
    
    if($initialWidth <= $model->sizes['normal']['width']) // изображение меньше максимальной ширины
      unset($model->sizes['full']);

    foreach($model->sizes as $sizeName => $size)
      $wideImage->resize($size['width'], $size['height'], 'inside', 'down')->saveToFile($uploadPath . $size['prefix'] . $fileName, $model->quality[$ext]);
    
    $model->{$attribute} = $fileName;
  }
  
  /**
   *  @return HTML код текущей картинки
   */
  public function getHtml()
  {
    return CHtml::link(CHtml::image($this->normal), $this->full, array('rel'=>'prettyPhoto'));
  }
  
  /**
   *  @return ссылка на полное изображение (в пределах, установленных в настройках $sizes)
   */
  public function getFull()
  {
    if(file_exists($this->uploadPath.$this->sizes['full']['prefix'].$this->name))
      return $this->imagesPath.$this->sizes['full']['prefix'].$this->name;
    return $this->normal;
  }
  
  /**
   *  @return ссылка на превьюшку изображения
   */
  public function getThumb()
  {
    return $this->imagesPath.$this->sizes['thumb']['prefix'].$this->name;
  }
  
  /**
   *  @return ссылка на изображение для вставки в текст
   */
  public function getNormal()
  {
    return $this->imagesPath.$this->sizes['normal']['prefix'].$this->name;
  }
  
  /**
   *  @return uri папки с картинками
   */
  public function getImagesPath()
  {
    return Yii::app()->baseUrl.'/uploads/imageuploads/';
  }
  
  /**
   *  @return путь к папке для загрузки файлов
   */
  public function getUploadPath()
  {
    //FIXME эта функция должна быть юзабельна для любой модели
    $dir = Yii::getPathOfAlias('webroot') . '/uploads/imageuploads/';
    foreach($this->sizes as $sizeName => $size)
      if(!is_dir($dir.$size['prefix']))
        mkdir($dir.$size['prefix']); // создаем директорию для картинок
    return $dir;
  }
  
  /**
   *  Отключает компонент WebLog
   */
  static function turnOffWebLog()
  {
    foreach (Yii::app()->log->routes as $route) 
    {
      if ($route instanceof CWebLogRoute) 
      {
        $route->enabled = false;
      }
    }
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

		$criteria->compare('name',$this->name,true);
		$criteria->compare('source',$this->source,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}