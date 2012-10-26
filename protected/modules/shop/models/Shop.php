<?php

/**
 * This is the model class for table "shop".
 *
 * The followings are the available columns in table 'shop':
 * @property string $id
 * @property string $supplier_id
 * @property string $edit_date
 * @property string $add_date
 * @property string $page_title
 * @property string $page_alias
 * @property string $description
 * @property string $waranty
 * @property integer $price
 * @property integer $cat_id
 * @property integer $brand_id
 * @property string $product_name
 * @property string $rating
 * @property string $status
 * @property string $shop_extra
 *
 * The followings are the available model relations:
 * @property ShopBrand $brand
 * @property ShopCategorie $cat
 * @property ShopComment[] $shopComments
 * @property ShopRating[] $shopRatings
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.ShopController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Shop extends CActiveRecord
{
  // Переменные, которые заполняются значениями из поля
  public $waranty;
  public $model_code;
  public $review;
  public $video;
  public $photo;
  public $prId; // id Продукта (по прайсу)
  public $uImage; // поле загрузки изображения
  
  /**
   *  Переменные специально для поиска и фильтрации с помощью search()
   */
  public $user_search; // свойство для реализации фильтрации по имени юзера
  public $cat_search; // свойство для реализации фильтрации по имени категории
  public $brand_search; // свойство для реализации фильтрации по имени бренда
  public $supplier_search; // свойство для реализации фильтрации по имени поставщика
  // фильтрация по диапазонам дат
  public $date_add_from;
  public $date_add_to;
  public $date_edit_from;
  public $date_edit_to;
  
  /**
   *  Поля для установки цены с помощью слайдера (в виджете фильтра)
   */
  public $priceMin;
  public $priceMax;
  protected $_minmax;
  protected $min;
  protected $max;
  
  // Дириктория для загрузки изображений и превьюшек
  public static $uploadsUrl = '/uploads/shop/';
  //public $char; //характеристики текущего товара
  
  const STATUS_DRAFT=1;
  const STATUS_PUBLISHED=2;
  const STATUS_UNAVAIBLE=3;
  const STATUS_PREORDER=4;
  const STATUS_OUT_OF_PRODUCTION=5;
  
  
  protected $_statusNames = array(
    self::STATUS_DRAFT => '<span style="color:#с9с9с9;">Черновик</span>',
    self::STATUS_PUBLISHED => '<span style="color:#76B348;">Есть в наличии</span>',
    self::STATUS_UNAVAIBLE => '<span style="color:#FE5050;">Нет в наличии</span>',
    self::STATUS_PREORDER => '<span style="color:#19b6b8;">Под заказ</span>',
    self::STATUS_OUT_OF_PRODUCTION => '<span style="color:#с9с9с9;">Снят с производства</span>',
  );
  
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Shop the static model class
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
		return 'shop';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('page_title, page_alias, description, price, cat_id, brand_id, product_name, status, supplier_id, prId', 'required'),
			array('price, cat_id, brand_id, waranty, priceMin, priceMax', 'numerical'),
			array('review, video, model_code', 'length', 'max'=>128),
      array('page_title, page_alias, product_name', 'length', 'max'=>256),
			//!T: review, video = url нужно добавить это правило
			array('status', 'length', 'max'=>1),
      array('supplier_id', 'length', 'max'=>2),
      array('prId', 'length', 'max'=>5),
      array('id', 'length', 'max'=>7),
			//array('categorie, new*', 'safe'),
			array('uImage', 'file',
        'types'=>'jpg, gif, png',
        'maxSize'=>1024 * 1024 * 5, // 5 MB
        'allowEmpty'=>'true',
        'maxFiles' => 8,
        'tooLarge'=>'Файл весит больше 5 MB. Пожалуйста, загрузите файл меньшего размера.',
        'safe' => true,
			),
			/*array('modified','default',
              'value'=>new CDbExpression('NOW()'),
              'setOnEmpty'=>false,'on'=>'update'),
        array('created,modified','default',
              'value'=>new CDbExpression('NOW()'),
              'setOnEmpty'=>false,'on'=>'insert')*/
      array('page_alias', 'unique'),
      array('prId', 'idUniqValidator'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
      // используется в админке
			array('id, date_add_from, date_add_to, date_edit_from, date_edit_to, page_title, price, product_name, rating, status, user_search, cat_search, brand_search, supplier_search', 'safe', 'on'=>'search'),
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
			'brand' => array(self::BELONGS_TO, 'Brand', 'brand_id'),
			'cat' => array(self::BELONGS_TO, 'Categorie', 'cat_id'),
			'comments' => array(self::HAS_MANY, 'Comment', 'prod_id'),
			'charShema' => array(self::BELONGS_TO, 'CharShema', 'cat_id'),
			'char' => array(self::HAS_MANY, 'Char', array('prod_id'=>'id')),
			'user' => array(self::BELONGS_TO, 'User', array('user_id'=>'id')),
			'supplier' => array(self::BELONGS_TO, 'Supplier', array('supplier_id'=>'id')),
			'rVoteCount' => array(self::STAT, 'Rating', 'prod_id'), //T!: убрать строку
		);
	}
	
	public function scopes()
  {
    return array(
      'published'=>array(
        'condition'=>'status<>'.self::STATUS_DRAFT,
      ),
      'latest'=>array(
        'order'=>'add_date DESC',
      ),
      'lastEdited'=>array(
        'order'=>'edit_date DESC',
      ),
    );
  }
  
  /**
   *  Проверяет уникальность идентификатора продукта
   */
  public function idUniqValidator($attribute,$params)
  {
    // Составляем id из prId (идентификатор продукта) и supplier_id (идентификатор поставщика)
    $id = self::genId($this->prId, $this->supplier_id);
    
    if(!$this->isNewRecord && (int)$id==(int)$this->oldPrimaryKey) return true;
    if($attribute != 'prId')
      throw new CException('idUniqValidator should be used on prId attribute');
    
    if(Shop::model()->findByPk($id))
      $this->addError($attribute, 'Продукт с таким кодом уже существует');
  }
  
  /**
   *  Генерирует конечный id товара из id поставщика и id товара у поставщика
   * 
   *  @param integer $prId id Товара
   *  @param integer $supplier_id id поставщика
   *
   *  @return integer $id pkid товара
   */
  static function genId($prId, $supplier_id)
  {
    return str_pad($supplier_id, 2, "0", STR_PAD_LEFT) . str_pad($prId, 5, "0", STR_PAD_LEFT);
  }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'Код',
			'edit_date' => 'Дата редактирования',
			'add_date' => 'Дата добавления',
			'page_title' => 'Title страницы',
			'page_alias' => 'ЧПУ страницы',
			'description' => 'Описание товара',
			'waranty' => 'Гарантия (мес.)',
      'model_code' => 'Код модели от производителя',
			'price' => 'Цена, грн.',
			'cat_id' => 'Категория',
			'brand_id' => 'Бренд',
      'supplier_id' => 'Поставщик',
			'product_name' => 'Название продукта',
			'rating' => 'Rating',
			'status' => 'Статус',
			'statusName' => 'Статус',
			'shop_extra' => 'Extra',
			'uImage' => 'Изображения товара',
			'photo' => 'Фото',
			'user_search' => 'Добавил',
			'cat_search' => 'Категория',
			'brand_search' => 'Бренд',
			'supplier_search' => 'Поставщик',
			'prId' => 'Код продукта',
			'priceMin' => 'От',
			'priceMax' => 'До',
		);
	}
	
	/**
	 * @return array типы полей для форм администрирования модуля
	 */
	public function getFieldTypes()
	{
		return array(
			'page_title' => 'text',
			'product_name' => 'text',
			'page_alias' => 'translit',
			'status' => array(
			  'dropdownlist',
			  'items' => $this->statusNames,
			),
			'supplier_id' => array(
			  'dropdownlist',
			  'items' => Supplier::model()->suppliersList,
			),
			'prId' => 'text',
			'brand_id' => array(
			  'dropdownlist',
			  'items' => Brand::model()->brandsList,
			  'empty' => '--Выберите бренд--',
			),
			// Поле в которое мы будем динамически подставлять значение cat_id
			'cat_id' => array(
			  'text',
			  'attributes'=>array(
			    'style'=>'display:none;',
			    //'disabled'=>'disabled'
			  )
			), 
			'waranty' => 'text',
      'model_code' => 'text',
			'price' => 'text',
			'uImage' => array(
			  'file',
			  'attributes'=>array(
			    'multiple'=>'multiple',
			    'name'=>'Shop[uImage][]',
			  ),
			),
			'html:<div>' . $this->showImages() . '</div>',
			/*'char' => array(
			  'form',
			  'model' => CharShema::model()->prodId(1)->findAllByCat(1),
			),*/
			'html:<div id="char_update"></div>',
			'description' => 'textarea',
		);
	}
	
	protected function showImages()
	{
	  $str = '<ul class="filseList">';
	  foreach($this->photo as $src)
	  {
	    $str .= '<li>' .CHtml::image($this->uploadsUrl.$src, 'Изображение', array('width'=>'100')) . '<strong>' . $src . '</strong><a href="" class="icon_delete" fname="'.$src.'"></a></li>';
	  }
	  $str .= '</ul>';
	  return $str;
	}
	
	/**
   * Возвращает текстовое представление статуса
   */
	public function getStatusName()
	{
	  return $this->_statusNames[$this->status];
	}
	
	/*
	*  используется для фильтра в CGridView, а так же при добавлении товара
	*/
  public static function getStatusNames() {
    return array(
      self::STATUS_DRAFT => 'Черновик',
      self::STATUS_PUBLISHED => 'Есть в наличии',
      self::STATUS_UNAVAIBLE => 'Нет в наличии',
      self::STATUS_PREORDER => 'Под заказ',
      self::STATUS_OUT_OF_PRODUCTION => 'Снят с производства',
    );
  }
  
  /**
   *  Выводит виджет с рейтингом
   */
  public function ratingWidget($controller)
  {
    $controller->widget('application.widgets.EStarRating',array(
      'name'=>'shop_product_rating'.uniqid(),
      'minRating' => '1',
    	'maxRating' => '5',
    	'ratingStepSize' => '1',
    	'value' => $this->ratingVal, // mark 1...5
    	'allowEmpty'=>false,
    	'titles'=>array(1=>'Ужасно', 'Плохо', 'Нормально', 'Хорошо', 'Отлично'),
    	'readOnly'=>true,
    	'cssFile'=>false,
    ));
    echo '<span style="vertical-align: 3px;">(' . $this->votesCount . ')</span>';
  }
  
  /**
   *  @return string количество проголосовавших юзеров
   */
  public function getVotesCount()
  {
    $rating[0] = 0;
    if($this->rating)
      $rating = explode('.', (string)$this->rating);
    
    return $rating[0];
  }
  
  /**
   *  @return string рейтинг
   */
  public function getRatingVal()
  {
    $rating = explode('.', (string)$this->rating);
    return $rating[1]/100;
  }
  
  /**
   *  Возвращает максимальное/минимальное значение характеристики в зависимости от $minmax
   *  $minmax может принимать значения min или max
   */
  protected function range($minmax)
  {
    if(empty($this->_minmax))
    {
      $criteria = new CDbCriteria;
      $criteria->select = 'MIN(CAST( price AS DECIMAL )) AS `min`, MAX(CAST( price AS DECIMAL )) AS `max`';
      if(isset($this->cat_id))
        $criteria->compare('cat_id', $this->cat_id);
      if(isset($this->brand_id))
        $criteria->compare('brand_id', $this->brand_id);
      $this->_minmax = $this->find($criteria);
    }
    return (float)$this->_minmax->$minmax;
  }
  
  public function getMinPriceVal()
  {
    return $this->range('min');
  }
  
  public function getMaxPriceVal()
  {
    return $this->range('max');
  }
		
	/**
   * Возвращает url страницы материала
   */
	public function getViewUrl()
  {
    return Yii::app()->createUrl('shop/' . $this->page_alias);
  }
  
  /**
   * Возвращает uri загрузки файлов
   */
  public function getUploadsUrl()
  {
    return self::$uploadsUrl;
  }
  
  /**
	 *  Расшифровываем данные поля extra
	 */
	protected function afterFind() 
	{
	  $extra = unserialize($this->shop_extra);
	  $this->waranty = $extra['waranty'];
    $this->model_code = $extra['model_code'];
    $this->review = $extra['review'];
    $this->video = $extra['video'];
    $this->photo = $extra['photo'];
    $this->id = str_pad($this->id, 7, "0", STR_PAD_LEFT);
    $this->prId = substr($this->id, 2);
    if(!is_array($this->photo)) $this->photo = array();
	}
	
	/**
	 *  Сериализуем shop_extra
	 *  Обновляем даты
   *  Добавляем автора материала
	 */
	protected function beforeSave()
  {
    if(parent::beforeSave())
    {
      if($this->isNewRecord)
      {
        $this->add_date=$this->edit_date=new CDbExpression('NOW()');
        $this->user_id=Yii::app()->user->id;
      }
      else
        $this->edit_date=new CDbExpression('NOW()');
        
      $extra['waranty'] = $this->waranty;
      $extra['model_code'] = $this->model_code;
      $extra['review'] = $this->review;
      $extra['video'] = $this->video;
      $extra['photo'] = $this->photo;
      // Составляем id из prId (идентификатор продукта) и supplier_id (идентификатор поставщика)
      $this->id = self::genId($this->prId, $this->supplier_id);
      $this->shop_extra = serialize($extra);
      return true;
    }
    else
      return false;
  }
  
  /**
	 * Для надежности транслитерируем поле product_alias
	 */
	protected function beforeValidate()
	{
	  if(parent::beforeValidate())
    {
      $this->page_alias = empty($this->page_alias) ? Translit::url($this->product_name) : Translit::url($this->page_alias);
      return true;
    }
    else
      return false;
	}
	
	protected function afterConstruct()
	{
	  if($this->isNewRecord)
        $this->photo = array();
	}
	
	/**
   *  @param string $name имя файла картинки
   *  @param integer $thumb ширина в пикселях превьюшки
	 *  @return полную ссылку к картинке и если надо, создает ее превьюшку
	 */
	public static function imgSrc($name = false, $thumb = false)
	{
	  $uploadPath = $_SERVER['DOCUMENT_ROOT'].Shop::$uploadsUrl;
	  if($thumb)
	  {
	    $src = Shop::$uploadsUrl . $thumb . '/' .$name;
	    if(!is_file($uploadPath . $thumb . DIRECTORY_SEPARATOR . $name)) // Создаем превьюшку
	    {
	      if(!is_file($uploadPath . $name)) return; // Не существует даже оргинала картинки / прерываем
	      
	      if(!is_dir($uploadPath . $thumb)) // создаем директорию для картинок
	        mkdir($uploadPath . $thumb, 0777);
	  
	      Yii::import('application.vendors.wideImage.WideImage'); // Библиотека управления изображениями
	  	
	      $sourcePath = pathinfo($name);
	      $wideImage = WideImage::load($uploadPath .  $name);
	      $white = $wideImage->allocateColor(255, 255, 255);
	      
	      // тут не учтены не квадратные разрешения
	      $wideImage->resize($thumb, $thumb)->resizeCanvas($thumb, $thumb, 'center', 'center', $white)->saveToFile($uploadPath . $thumb . DIRECTORY_SEPARATOR . $sourcePath['filename'].'.jpg', 75);
	    }
	  }
	  else
	    $src = Shop::$uploadsUrl .  $name;
	  return $src;
	}
  
  /**
   *  @return ссылку на картинку первой превьюшки
   */
  public function firstImgSrc($thumbWidth = false)
  {
    return Shop::imgSrc($this->photo[0], $thumbWidth);
  }
	
	/**
	*  Возвращает код img картинки по ее индексу
	**/
	public function img($thumbWidth = false, $index = false)
	{
    if (!count($this->photo)) return '';
	  if ($index === false) $index = key($this->photo);
	  return CHtml::image(Shop::imgSrc($this->photo[$index], $thumbWidth), $this->product_name, array('width' => ($thumbWidth ? $thumbWidth : '')));
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

		// фильтрация по промежуткам дат
    if((isset($this->date_add_from) && trim($this->date_add_from) != "") && (isset($this->date_add_to) && trim($this->date_add_to) != ""))
      $criteria->addBetweenCondition('t.add_date', ''.date_format(new DateTime($this->date_add_from), 'Y-m-d').'', ''.date_format(new DateTime($this->date_add_to), 'Y-m-d').'');
    if((isset($this->date_edit_from) && trim($this->date_edit_from) != "") && (isset($this->date_edit_to) && trim($this->date_edit_to) != ""))
      $criteria->addBetweenCondition('t.add_date', ''.date_format(new DateTime($this->date_edit_from), 'Y-m-d').'', ''.date_format(new DateTime($this->date_edit_to), 'Y-m-d').'');

		//$criteria->compare('page_title',$this->page_title,true);
		//$criteria->compare('page_alias',$this->page_alias,true);
		//$criteria->compare('description',$this->description,true);
		//$criteria->compare('waranty',$this->varanty,true);
		//$criteria->compare('price',$this->price);
    if(!empty($this->id))
      $criteria->compare('t.id',(int)$this->id, true);
		$criteria->compare('t.product_name',$this->product_name,true);
		$criteria->compare('t.rating',$this->rating,true);
		$criteria->compare('t.status',$this->status);
		
		// Критерии для фильтрации по related таблицам
		$criteria->compare( 'user.first_name', $this->user_search, true );
		$criteria->compare( 'brand.brand_name', $this->brand_search, true );
		$criteria->compare( 'cat.cat_name', $this->cat_search, true );
		$criteria->compare( 'supplier.id', $this->supplier_search, true );
		
		$criteria->with=array(
      'cat'=>array('select'=>'cat.cat_name'),
      'brand'=>array('select'=>'brand.brand_name'),
      'user'=>array('select'=>'user.first_name'),
      'supplier',
    );

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
        'attributes'=>array(
          'user_search'=>array(
            'asc'=>'user.first_name',
            'desc'=>'user.first_name DESC',
          ),
          'cat_search'=>array(
            'asc'=>'cat.cat_name',
            'desc'=>'cat.cat_name DESC',
          ),
          'brand_search'=>array(
            'asc'=>'brand.brand_name',
            'desc'=>'brand.brand_name DESC',
          ),
          'supplier_search'=>array(
            'asc'=>'supplier.name',
            'desc'=>'supplier.name DESC',
          ),
          '*',
        ),
      ),
		));
	}
}
