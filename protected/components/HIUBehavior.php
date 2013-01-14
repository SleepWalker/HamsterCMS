<?php
/**
 * HIUBehavior поведение, добавляющее моделям возможность загружать изображения
 * 
 * @uses CActiveRecordBehavior
 * @package hamster.modules.admin.components.HIUBehavior
 * @version $id$
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class HIUBehavior extends CActiveRecordBehavior
{
// TODO: CForm должна понимать, что у текущей модели есть это поведение. основная проблема в том, в какое именно место в форме вставлять это поле
  // поле для загружаемого изображения (используется для обеспечения валидации
  public $uImage;
  // имя поля через которое будет загружаться файл
  public $fileFieldName = 'uImage';
  
  // настройки качества изображений
  public $quality = array(
    'png' => 7,
    'jpg' => 75,
    'gif' => 256,
  );

  /**
   *  @var array $sizes
   *  Настройка размеров изображений.
   *  Пример:
   *   ...
   *   'sizes'=>array(
   *      'normal' => array(
   *        'width'=>625,
   *        'scale'=> 'any|down|up', // default: down
   *        'fit' => 'inside|fill|outside', // default: inside
   *        'crop' => true, // после ресайзинга обрежит картинку на width и height относительно центра (если crop==true, fit будет переключен в outside)
   *      ),
   *      'full' => array(
   *        'width'=>1024,
   *      ),
   *      'thumb' => array(
   *        'width' => 150,
   *        'height' => 150,
   *      ),
   *    ),
   *    ...
   */
  public $sizes;

  // Директория, в которой должны находться картинки (относительно uploads)
  public $dirName;

  // имя аттрибута, в котором будет храниться название файла картинки
  public $fileAtt;

  // формат, в который будут конвертироваться все изображения (jpg, png, gif). Если false - формат изображения не будет меняться
  public $forceExt = false;

  /**
   * Добавляет модели валидатор для поля с изображением 
   * @param model $owner 
   */
  public function attach($owner) {
    parent::attach($owner);

    // проверка присутствия всех обязательных параметров
    if(!is_array($this->sizes['normal']) || !is_array($this->sizes['full']))
      throw new CException('Отсутствуют обязательные элементы full и normal в массиве sizes');

    if(empty($this->fileAtt) || empty($this->dirName))
      throw new CException('Не заданы один или несколько из обязательных атрибутов: fileAtt, dirName');

    $owner = $this->getOwner();

    $validators = $owner->getValidatorList();

    $params = array(
      'types'=>'jpg, jpeg, gif, png',
      'maxSize'=>1024 * 1024 * 5, // 5 MB
      'maxFiles' => 1,
      'allowEmpty' => true, // TODO разобраться с этим параметром и сделать, что бы все работало без него (если он отключен валидация жалуется...)
      'tooLarge'=>'Файл весит больше 5 MB. Пожалуйста, загрузите файл меньшего размера.',
      'safe' => true,
    );

    $validator = CValidator::createValidator('file', $owner, 'uImage',$params );
    $validators->add($validator);

    // всем полям, кроме normal по дефолту добавляем преффиксы
    foreach($this->sizes as $id => &$size)
    {
      if(empty($size['fit']))
        $size['fit'] = 'inside';

      if(empty($size['scale']))
        $size['scale'] = 'down';

      if($size['crop'] === true)
        $size['fit'] = 'outside';

      if($id == 'normal')
        continue;
      
      $size['prefix'] = $id .'/';
    }
  }

  /**
	 * Новым моделям инициализируем имя файла
	 */
  public function beforeValidate(CEvent $event)
  {
    $model = $event->sender;
    if($model->isNewRecord)
      $this->generateFileName($model);
  }

  /**
	 * Сохраняем загруженное изображение
	 */
	public function beforeSave(CEvent $event)
  {
    $model = $event->sender;
    $model->processUpload($model, $this->fileAtt);
    $event->isValid = true;
  }
  
  /**
	 * Удаляем картинки, загруженные с моделью 
	 */
	public function afterDelete(CEvent $event)
	{
    $model = $event->sender;
    $this->deleteImage($model->{$this->fileAtt});
	}

  /**
   * Обрабатывает загруженную картинку и сохраняет ее название в $attribute
   * @param string $model модуль, в которую загружается картинка
   *    у модели должны быть поля: $uploadPath, $sizes, $quality
   *    так же у моели должно быть заполненно поле CUploadedFile $uImage
   * @param string $attribute имя атрибута в котором хранится название файла картинки
   */
  protected function processUpload($model, $attribute)
  {  
    if(!preg_match('%/$%', $model->uploadPath)) $uploadPath = $model->uploadPath.'/';
    else $uploadPath = $model->uploadPath;
    
    // вернет имя для файла изображения и если надо сгенерирует новое, а старое удалит
    $fileName = $this->fileName;

    if(!$model->uImage)
      // Юзер не производил загрузки новой картинки
      return;
    
    Yii::import('application.vendors.wideImage.WideImage');
    $wideImage = WideImage::load($model->uImage->tempName);
    $initialWidth = $wideImage->getWidth();
    
    if($initialWidth <= $this->sizes['normal']['width']) // изображение меньше максимальной ширины
      unset($this->sizes['full']);

    foreach($this->sizes as $size)
    {
      if($size['crop'] == true)
      {
        $cropWidth = $size['width'];
        $cropHeight = $size['height'];
      }
      else
        $cropWidth = $cropHeight = '100%';

      $wideImage->resize($size['width'], $size['height'], $size['fit'], $size['scale'])
        ->crop('center', 'center', $cropWidth, $cropHeight)
        ->saveToFile($uploadPath . $size['prefix'] . $fileName, $this->qualityForFile($fileName));
    }
  }

  /**
   * Возвращает качество изображения в зависимости от его расширения  
   * 
   * @param mixed $filename имя файла
   * @access protected
   * @return string
   */
  protected function qualityForFile($fileName)
  {
    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
    return $this->quality[$ext];
  }

  /**
   * Генерирует уникальное имя файла для картинки с учетом заданных в настройках расширений
   * 
   * @param CActiveRecord $model
   * @access protected
   * @return string имя файла
   */
  protected function generateFileName($model)
  {
    $model->uImage=CUploadedFile::getInstance($model, $this->fileFieldName);	
    if(!$model->uImage)
      $model->uImage=CUploadedFile::getInstanceByName($this->fileFieldName);

    if(!$model->uImage) return; // юзер ничего не загрузил

    //TODO: здесь можно проверять загруженно ли изображение (в том случае, если это обязательно)
      
    if(empty($model->{$this->fileAtt}))
    {
      if($this->forceExt) $ext = $this->forceExt;
      else{
        $ext = $model->uImage->extensionName;
        if($ext == 'jpeg') $ext = 'jpg';
      }

      $model->{$this->fileAtt} = uniqid().'.'.$ext;
    }
  }

  /**
   * Удаляет изображение и все его копии (превьюшки и т.д.) из фс по его имени  
   * 
   * @param string $filename имя файла
   * @access protected
   * @return void
   */
  protected function deleteImage($fileName)
  {
    foreach($this->sizes as $size)
    {
      $file = $this->uploadPath.$size['prefix'].$fileName;
      if(file_exists($file) && !is_dir($file))
        unlink($file); // удаляем картинку
    }
    $this->owner->{$this->fileAtt} = ''; // удаляем из модели
  }

  /**
   * Возвращает html код изображения по его размеру
   * 
   * @param string $size имя элемента из массива {@link $sizes}
   * @param string $alt alt аттрибут для тега img
   * @param array $htmlOptions массив дополнительных настроек для CHtml::image()
   * @access public
   * @return string html код
   */
  public function img($size = 'normal', $alt = '', array $htmlOptions = array())
  {
    if(($src = $this->src($size)) != '')
      return CHtml::image($src, $alt, $htmlOptions);
    else
      return '';
  }

  /**
   * Возвращает адрес картинки по ее размеру
   * 
   * @param string $size имя элемента из массива {@link $sizes}
   * @see $sizes
   * @access public
   * @return 
   */
  public function src($size = 'normal')
  {
    if(is_array($this->sizes[$size]) && !empty($this->filename))
    {
      $relFilePath = $this->sizes[$size]['prefix'].$this->filename;
      if($size == 'full' && !file_exists($this->uploadPath.$relFilePath))
        return $this->src('normal');
      return $this->uploadsUrl.$relFilePath;
    }

  }
  
  /**
   *  @return путь к папке для загрузки файлов
   */
  public function getUploadPath()
  {
    if(($dir = Yii::getPathOfAlias('webroot.uploads')) && !is_writable($dir))
      throw new CException("Нужны права на запись в директорию '$dir'");

    $dir = $dir . '/'.$this->dirName.'/';

    foreach($this->sizes as $sizeName => $size)
      if(!is_dir($dir.$size['prefix']))
        mkdir($dir.$size['prefix'], 0777, true); // создаем директорию для картинок

    return $dir;
  }
  
  /**
   *  @return uri папки с картинками
   */
  public function getUploadsUrl()
  {
    return Yii::app()->baseUrl.'/uploads/'.$this->dirName.'/';
  }

  /**
   * Возвращает имя файла картинки или генерирует новоей (если юзер загрузил картинку)
   * 
   * @see {@link generateFileName}
   * @access public
   * @return string имя файла картинки
   */
  public function getFileName()
  {
    $fileName = $this->owner->{$this->fileAtt};
    if(!empty($fileName) && $_POST[get_class($this->owner)]['uImage'] == 'delete')
    {
      $this->deleteImage($fileName);
      $fileName = '';
    }

    if(empty($fileName))
      $this->generateFileName($this->owner);
      
    return $this->owner->{$this->fileAtt};
  }
}
