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
	// поле для загружаемого изображения (используется для обеспечения валидации
	public $uImage;

	/**
	 * @var string $oldFilePath путь картинки который был в модели до того как было загруженно новое изображение
	 */
	protected $_oldFilePath;

	// имя поля через которое будет загружаться файл
	public $fileFieldName = 'uImage';

	/**
	 * @property string $noImageUrl ссылка на картинку, которая будет выводится, если поле картинки пустое
	 */
	public $noImageUrl;

	/**
	 * @var boolean $multiple если true, то к модели можно будет загрузить несколько изображений
	 * TODO
	 */
	public $multiple = false;
	
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
	 *      'original' => array(
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
		if(!is_array($this->sizes['normal']) || !is_array($this->sizes['original']))
			throw new CException('Отсутствуют обязательные элементы original и normal в массиве sizes');

		if(empty($this->fileAtt) || empty($this->dirName))
			throw new CException('Не заданы один или несколько из обязательных атрибутов: fileAtt, dirName');

		$owner = $this->getOwner();

		$this->_oldFilePath = $owner->{$this->fileAtt};

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

			if(isset($size['crop']) && $size['crop'] === true)
				$size['fit'] = 'outside';

			if($id == 'original')
				$size['prefix'] = '';
			else 
			$size['prefix'] = $id;

			if(!isset($size['height']))
				$size['height'] = null;

			if(!isset($size['width']))
				$size['width'] = null;
		}
	}

	/**
	 * Новым моделям инициализируем имя файла
	 */
	public function beforeValidate($event)
	{
		$model = $event->sender;

		if($this->_oldFilePath == $model->{$this->fileAtt} || empty($model->{$this->fileAtt})) 
		{
			$model->{$this->fileAtt} = null;
			$this->generateFileName($model);
		}
	}

	/**
	 * Сохраняем загруженное изображение
	 */
	public function beforeSave($event)
	{
		$model = $event->sender;
		$model->processUpload($model, $this->fileAtt);
		$event->isValid = true;
	}

	/**
	 * После сохранения присваиваем полю uImage ссылку на актуальное изображение  
	 * 
	 * @param CEvent $event 
	 * @access public
	 * @return void
	 */
	public function afterSave($event)
	{
		$model = $event->sender;
		$model->uImage = $model->{$this->fileAtt};
		$this->_oldFilePath = $model->{$this->fileAtt};
	}

	/**
	 * Заполняем поле uImage адресом текущего изображения  
	 * 
	 * @param CEvent $event 
	 * @access public
	 * @return void
	 */
	public function afterFind($event)
	{
		$model = $event->sender;
		$model->uImage = $model->{$this->fileAtt};
		if($put = Yii::app()->request->getPut('User'))
		{
			print_r($put);exit;
		}
	}
	
	/**
	 * Удаляем картинки, загруженные с моделью 
	 */
	public function afterDelete($event)
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
		$model->uImage = $this->getUploadedFile();

		// попробуем удалить старое изображение, если в запросе присутствуют соответствующие флаги
		$fileRelPath = $this->owner->{$this->fileAtt};
		if(!empty($fileRelPath) && isset($_POST[get_class($this->owner)]['uImage']) && $_POST[get_class($this->owner)]['uImage'] == 'delete')
		{
			$this->deleteImage($fileRelPath);
		}

		if(!$model->uImage || is_string($model->uImage))
			// Юзер не производил загрузки новой картинки
			return;

		
		$wideImage = $this->loadImage($model->uImage->tempName);
		//$initialWidth = $wideImage->getWidth();
		
		//if($initialWidth <= $this->sizes['normal']['width']) // изображение меньше максимальной ширины
		//  unset($this->sizes['original']);

		foreach($this->sizes as $size)
		{
			$this->resizeTo($size, $wideImage);
		}

		// почистим старые изображения
		if(!empty($this->_oldFilePath))
			$this->deleteImage($this->_oldFilePath);
	}

	/**
	 * Создает изображение заданного размера $size.
	 * 
	 * @param mixed $size псевдоним размера или массив с настройками размера
	 * @param mixed $filePath путь к файлу картинки-исходника или обьект WideImage
	 * @access protected
	 * @return void
	 */
	protected function resizeTo($size, $filePath)
	{
		$uploadPath = rtrim($this->owner->uploadPath, '/').'/';

		if(is_string($filePath))
		{
			$wideImage = $this->loadImage($filePath);
		}
		else
		{
			// в качестве аргумента был передан обьект WideImage
			$wideImage = $filePath;
			unset($filePath);
		}

		if(is_string($size))
		{
			// добываем настройки для псевдонима размера
			$size = $this->sizes[$size];
		}

		// вернет имя для файла изображения и если надо сгенерирует новое, а старое удалит
		$fileRelPath = $this->fileRelPath;

		if(isset($size['crop']) && $size['crop'] == true)
		{
			$cropWidth = $size['width'];
			$cropHeight = $size['height'];
		}
		else
			$cropWidth = $cropHeight = '100%';



		$destinationDir = rtrim($uploadPath . DIRECTORY_SEPARATOR . dirname($fileRelPath) . DIRECTORY_SEPARATOR . $size['prefix'], DIRECTORY_SEPARATOR); // $size['prefix'] может быть пустым, потому trim

		if(!is_dir($destinationDir) && !is_file($destinationDir))
			mkdir($destinationDir, 0777, true); // создаем директорию для картинок

		$destination = $destinationDir.DIRECTORY_SEPARATOR.$this->fileName;

		$wideImage->resize($size['width'], $size['height'], $size['fit'], $size['scale'])
			->crop('center', 'center', $cropWidth, $cropHeight)
			->saveToFile($destination, $this->qualityForFile($destination));
	}

	/**
	 * Возвращает качество изображения в зависимости от его расширения  
	 * 
	 * @param mixed $fileName имя файла
	 * @access protected
	 * @return string
	 */
	protected function qualityForFile($fileName)
	{
		$ext = pathinfo($fileName, PATHINFO_EXTENSION);
		return $this->quality[$ext];
	}

	/**
	 * Возвращает имя файла картинки или генерирует новое (если юзер загрузил картинку)
	 * 
	 * @see {@link generateFileName}
	 * @access public
	 * @return string имя файла картинки
	 */
	public function getFileName()
	{
		$fileName = $this->owner->{$this->fileAtt};

		if(empty($fileName) && !$this->generateFileName($this->owner))
			return null;
			
		return basename($this->owner->{$this->fileAtt});
	}

	/**
	 * Возвращает имя файла с относительным путем (например 2013/09/fileName.ext) картинки или генерирует новые (если юзер загрузил картинку)
	 * 
	 * @see {@link generateFileName}
	 * @access public
	 * @return string имя файла картинки
	 */
	public function getFileRelPath()
	{
		$fileRelPath = $this->owner->{$this->fileAtt};

		if(empty($fileRelPath) && !$this->generateFileName($this->owner))
			return null;
			
		return $this->owner->{$this->fileAtt};
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
		$model->uImage = $this->getUploadedFile();

		if(!$model->uImage) return null; // юзер ничего не загрузил

		//TODO: здесь можно проверять загруженно ли изображение (в том случае, если это обязательно)

		$return = null;
		if(empty($model->{$this->fileAtt}))
		{
			if($this->forceExt) $ext = $this->forceExt;
			else{
				$ext = $model->uImage->extensionName;
				if($ext == 'jpeg') $ext = 'jpg';
			}


			$folder = $this->dirName.DIRECTORY_SEPARATOR.date('Y').DIRECTORY_SEPARATOR.date('m').DIRECTORY_SEPARATOR; 
			
			if (!file_exists($this->uploadPath.DIRECTORY_SEPARATOR.$folder) && 
				!is_dir($this->uploadPath.DIRECTORY_SEPARATOR.$folder)){
				mkdir($this->uploadPath.DIRECTORY_SEPARATOR.$folder,0777,true);
			}

			$model->{$this->fileAtt} = $folder . uniqid().'.'.$ext;

			$return = $model->{$this->fileAtt};
		}
		
		return $return;
	}

	/**
	 * @return возвращает обьект CUploadedFile для загруженного файла
	 */
	protected function getUploadedFile()
	{
		$uImage=CUploadedFile::getInstance($this->owner, $this->fileFieldName);  
		if(!$uImage)
			$uImage=CUploadedFile::getInstanceByName($this->fileFieldName);

		return $uImage;
	}

	/**
	 * Удаляет изображение и все его копии (превьюшки и т.д.) из фс по его имени  
	 * 
	 * @param string $fileRelPath относительный путь файла
	 * @access protected
	 * @return void
	 */
	protected function deleteImage($fileRelPath)
	{
		foreach($this->sizes as $size)
		{
			$file = $this->uploadPath.DIRECTORY_SEPARATOR.dirname($fileRelPath).DIRECTORY_SEPARATOR.$size['prefix'].DIRECTORY_SEPARATOR.basename($fileRelPath);

			if(file_exists($file) && !is_dir($file))
				unlink($file); // удаляем картинку
		}

		if($fileRelPath == $this->owner->{$this->fileAtt})
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
		if(($src = $this->src($size)))
		{
			$htmlOptions = CMap::mergeArray(
				array(
					'width' => $this->sizes[$size]['width'],
				),
				$htmlOptions
			);
			if(isset($htmlOptions['src']))
				$src = $htmlOptions['src'];

			return CHtml::image($src, $alt, $htmlOptions);
		}
		else
			return '';
	}

	/**
	 * Возвращает false, если к модели не прикрепленно ниодной картинки
	 */
	public function getHasImg()
	{
		return !empty($this->owner->{$this->fileAtt});
	}

	/**
	 * Возвращает адрес картинки по ее размеру
	 * 
	 * @param string $size имя элемента из массива {@link $sizes}
	 * @see $sizes
	 * @access public
	 * @return mixed uri картинки, url заглушки или false
	 */
	public function src($size = 'normal')
	{
		$fileRelPath = $this->owner->{$this->fileAtt};
		$fileName = basename($fileRelPath);
		// TODO: пускай скрипт генерирует картинку, если такой не существует
		if(is_array($this->sizes[$size]) && !empty($fileName))
		{
			if(strpos($fileRelPath, '://'))
			{
				// возвращаем просто значение поле, так как в нем находиться ссылка
				return $this->owner->{$this->fileAtt};
			}else{
				$filePath = dirname($this->fileRelPath).DIRECTORY_SEPARATOR.$this->sizes[$size]['prefix'].DIRECTORY_SEPARATOR.$fileName;
				$fileUrlPath = str_replace(DIRECTORY_SEPARATOR, '/', $filePath);
				$filePath = $this->uploadPath.DIRECTORY_SEPARATOR.$filePath;
				if(!file_exists($filePath) && $size != 'original')
				{
					$this->resizeTo($size, $this->uploadPath.DIRECTORY_SEPARATOR.$this->fileRelPath);
				}

				return $this->uploadsUrl . '/' . $fileUrlPath;
			}
		}
		elseif(isset($this->noImageUrl))
		{
			return $this->noImageUrl;
		}

		return false;
	}

	public function getSizes($size = 'normal')
	{
		$size = $this->sizes[$size];
		return array('width' => $size['width'], 'height' => $size['height']);
	}
	
	/**
	 *  @return путь к папке для загрузки файлов
	 */
	public function getUploadPath()
	{
		$dir = Yii::getPathOfAlias('webroot.uploads');
		if(!is_writable($dir))
			throw new CException("Нужны права на запись в директорию '$dir'");

		return $dir;
	}
	
	/**
	 *  @return uri папки с картинками
	 */
	public function getUploadsUrl()
	{
		return self::getUploadsBaseUrl();
	}

	/**
	 * @return базовый uri для всех загрузок
	 */
	public static function getUploadsBaseUrl()
	{
		return Yii::app()->baseUrl.'/uploads';
	}

	/**
	 * Возвращает экземпляр обьекта WideImage для изображения $imagePath  
	 * 
	 * @param mixed $imagePath
	 * @access protected
	 * @return void
	 */
	protected function loadImage($imagePath)
	{
		Yii::import('application.vendors.wideImage.WideImage');
		return WideImage::load($imagePath);
	}
}
