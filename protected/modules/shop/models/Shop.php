<?php

/**
 * This is the model class for table "shop".
 *
 * The followings are the available columns in table 'shop':
 * @property string $id
 * @property string $code
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
 * @package    shop.models
 */

use user\models\User;

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

    const STATUS_AVAIBLE = 1;
    const STATUS_PUBLISHED = 1; // на всякий случай, может где-то пропустил
    const STATUS_PREORDER = 2;
    const STATUS_UNAVAIBLE = 3;
    const STATUS_OUT_OF_PRODUCTION = 4;
    const STATUS_DRAFT = 5;

    protected $_statusNames = array(
        self::STATUS_DRAFT => '<span class="status_draft">Черновик</span>',
        self::STATUS_AVAIBLE => '<span class="status_avaible">Есть в наличии</span>',
        self::STATUS_UNAVAIBLE => '<span class="status_unavaible">Нет в наличии</span>',
        self::STATUS_PREORDER => '<span class="status_preorder" style="color:#19b6b8;">Под заказ</span>',
        self::STATUS_OUT_OF_PRODUCTION => '<span class="status_outOfProduction">Снят с производства</span>',
    );

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Shop the static model class
     */
    public static function model($className = __CLASS__)
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
            array('price, user_id, cat_id, brand_id, waranty, priceMin, priceMax', 'numerical'),
            array('review, video, model_code', 'length', 'max' => 128),
            array('page_title, page_alias, product_name', 'length', 'max' => 256),
            //!T: review, video = url нужно добавить это правило
            array('status', 'length', 'max' => 1),
            array('supplier_id', 'length', 'max' => 2),
            //Длину кодов проверим в idValidator
            //array('prId', 'length', 'max'=>5),
            //array('id', 'length', 'max'=>7),
            //array('categorie, new*', 'safe'),
            array('uImage', 'file',
                'types' => 'jpg, gif, png',
                'maxSize' => 1024 * 1024 * 5, // 5 MB
                'allowEmpty' => 'true',
                'maxFiles' => 8,
                'tooLarge' => 'Файл весит больше 5 MB. Пожалуйста, загрузите файл меньшего размера.',
                'safe' => true,
            ),
            /*array('modified','default',
            'value'=>new CDbExpression('NOW()'),
            'setOnEmpty'=>false,'on'=>'update'),
            array('created,modified','default',
            'value'=>new CDbExpression('NOW()'),
            'setOnEmpty'=>false,'on'=>'insert')*/
            array('page_alias', 'unique'),
            array('prId, code', 'idValidator'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            // используется в админке
            array('code, date_add_from, date_add_to, date_edit_from, date_edit_to, page_title, price, product_name, rating, status, user_search, cat_search, brand_search, supplier_search', 'safe', 'on' => 'search'),
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
            'char' => array(self::HAS_MANY, 'Char', array('prod_id' => 'id')),
            'user' => array(self::BELONGS_TO, User::class, 'user_id'),
            'supplier' => array(self::BELONGS_TO, 'Supplier', array('supplier_id' => 'id')),
            'rVoteCount' => array(self::STAT, 'Rating', 'prod_id'), //T!: убрать строку
        );
    }

    public function scopes()
    {
        return array(
            'published' => array(
                'condition' => 'status<>' . self::STATUS_DRAFT,
            ),
            'latest' => array(
                'order' => 'add_date DESC',
            ),
            'lastEdited' => array(
                'order' => 'edit_date DESC',
            ),
        );
    }

    /**
     *  Проверяет уникальность идентификатора продукта
     *  а так же соответствию формату, заданному в настройках модуля
     */
    public function idValidator($attribute, $params)
    {
        if ($attribute != 'prId' && $attribute != 'code') {
            throw new CException('idValidator should be used on prId or code attribute');
        }

        // Составляем code из prId (идентификатор продукта) и supplier_id (идентификатор поставщика)
        $code = self::genCode($this->prId, $this->supplier_id);

        if ($attribute == 'prId') {
            if (($model = Shop::model()->findByAttributes(array('code' => $code))) && $model->primaryKey != $this->primaryKey) {
                $this->addError($attribute, 'Продукт с таким кодом уже существует');
            }

            $codeFormat = Yii::app()->modules['shop']['params']['codeFormat'];

            if ($codeFormat == 'supplierPreffix' || $codeFormat == 'zerofill') {
                // проверим длину prId
                $maxLength = Yii::app()->modules['shop']['params']['codeLength'];
                if ($codeFormat == 'supplierPreffix') {
                    $maxLength -= 2;
                }

                if (strlen($this->prId) > $maxLength) {
                    $this->addError($attribute, 'Слишком длинный код');
                }

            }
        }
    }

    /**
     *  Генерирует конечный code товара из id поставщика и prId товара у поставщика
     *
     *  @param integer $prId code Товара
     *  @param integer $supplier_id id поставщика
     *
     *  @return integer $id pkid товара
     */
    public static function genCode($prId, $supplier_id)
    {
        switch (Yii::app()->modules['shop']['params']['codeFormat']) {
            case 'supplierPreffix':
                return sprintf("%'02s%'0" . (Yii::app()->modules['shop']['params']['codeLength'] - 2) . "s", $supplier_id, $priId); //str_pad($supplier_id, 2, "0", STR_PAD_LEFT) . str_pad($prId, 5, "0", STR_PAD_LEFT);
                break;
            case 'zerofill':
                return sprintf("%'0" . Yii::app()->modules['shop']['params']['codeLength'] . "s", $priId);
                break;
            default:
                return $prId;
        }
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'code' => 'Код',
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
                'attributes' => array(
                    'style' => 'display:none;',
                    //'disabled'=>'disabled'
                ),
            ),
            'waranty' => 'text',
            'model_code' => 'text',
            'price' => 'text',
            'uImage' => array(
                'file',
                'attributes' => array(
                    'multiple' => 'multiple',
                    'name' => 'Shop[uImage][]',
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
        foreach ($this->photo as $src) {
            $str .= '<li>' . CHtml::image($this->uploadsUrl . $src, 'Изображение', array('width' => '100')) . '<strong>' . $src . '</strong><a href="" class="icon_delete" fname="' . $src . '"></a></li>';
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
    public static function getStatusNames()
    {
        return array(
            self::STATUS_DRAFT => 'Черновик',
            self::STATUS_AVAIBLE => 'Есть в наличии',
            self::STATUS_UNAVAIBLE => 'Нет в наличии',
            self::STATUS_PREORDER => 'Под заказ',
            self::STATUS_OUT_OF_PRODUCTION => 'Снят с производства',
        );
    }

    /**
     *  Выводит виджет с рейтингом
     */
    public function ratingWidget()
    {
        Yii::app()->controller->widget('application.widgets.EStarRating', array(
            'name' => 'shop_product_rating',
            'value' => $this->ratingVal, // mark 1...5
            'readOnly' => true,
        ));
        echo '<span style="vertical-align: 3px;">(' . $this->votesCount . ')</span>';
    }

    /**
     *  @return string количество проголосовавших юзеров
     */
    public function getVotesCount()
    {
        $rating[0] = 0;
        if ($this->rating) {
            $rating = explode('.', (string) $this->rating);
        }

        return $rating[0];
    }

    /**
     *  @return string рейтинг
     */
    public function getRatingVal()
    {
        $rating = explode('.', (string) $this->rating);
        return $rating[1] / 100;
    }

    /**
     *  Возвращает максимальное/минимальное значение характеристики в зависимости от $minmax
     *  $minmax может принимать значения min или max
     */
    protected function range($minmax)
    {
        if (empty($this->_minmax)) {
            $criteria = new CDbCriteria;
            $criteria->select = 'MIN(CAST( price AS DECIMAL )) AS `min`, MAX(CAST( price AS DECIMAL )) AS `max`';
            if (isset($this->cat_id)) {
                $criteria->compare('cat_id', $this->cat_id);
            }

            if (isset($this->brand_id)) {
                $criteria->compare('brand_id', $this->brand_id);
            }

            $this->_minmax = $this->find($criteria);
        }
        return (float) $this->_minmax->$minmax;
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
        $this->prId = $this->code;

        // Форматирование кода в зависимости от формата, указанного в админке
        $codeFormat = Yii::app()->modules['shop']['params']['codeFormat'];
        if ($codeFormat == 'supplierPreffix' || $codeFormat == 'zerofill') {
            $this->prId = $this->code = sprintf("%'0" . Yii::app()->modules['shop']['params']['codeLength'] . "s", $this->code);
        }

        if ($codeFormat == 'supplierPreffix') {
            list($null, $this->prId) = sscanf($this->code, "%2d%" . (Yii::app()->modules['shop']['params']['codeLength'] - 2) . "s");
        }

        if (!is_array($this->photo)) {
            $this->photo = array();
        }

    }

    /**
     *  Сериализуем shop_extra
     *  Обновляем даты
     *  Добавляем автора материала
     */
    protected function beforeSave()
    {
        if (parent::beforeSave()) {
            if ($this->isNewRecord) {
                $this->add_date = $this->edit_date = new CDbExpression('NOW()');
                $this->user_id = Yii::app()->user->id;
            } else {
                $this->edit_date = new CDbExpression('NOW()');
            }

            $extra['waranty'] = $this->waranty;
            $extra['model_code'] = $this->model_code;
            $extra['review'] = $this->review;
            $extra['video'] = $this->video;
            $extra['photo'] = $this->photo;
            // Составляем code из prId (идентификатор продукта) и supplier_id (идентификатор поставщика)
            $this->code = self::genCode($this->prId, $this->supplier_id);
            $this->shop_extra = serialize($extra);
            return true;
        } else {
            return false;
        }

    }

    /**
     * Для надежности транслитерируем поле product_alias
     */
    protected function beforeValidate()
    {
        if (parent::beforeValidate()) {
            $this->page_alias = empty($this->page_alias) ? Translit::url($this->product_name) : Translit::url($this->page_alias);
            return true;
        } else {
            return false;
        }

    }

    protected function afterConstruct()
    {
        if ($this->isNewRecord) {
            $this->photo = array();
        }

    }

    /**
     *  @param string $name имя файла картинки
     *  @param integer $thumb ширина в пикселях превьюшки
     *  @return полную ссылку к картинке и если надо, создает ее превьюшку
     */
    public static function imgSrc($name = false, $thumb = false)
    {
        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . Shop::$uploadsUrl;
        if ($thumb) {
            $src = Shop::$uploadsUrl . $thumb . '/' . $name;
            if (!is_file($uploadPath . $thumb . DIRECTORY_SEPARATOR . $name)) {
                // Создаем превьюшку
                if (!is_file($uploadPath . $name)) {
                    return;
                }
                // Не существует даже оргинала картинки / прерываем

                if (!is_dir($uploadPath . $thumb)) {
                    // создаем директорию для картинок
                    mkdir($uploadPath . $thumb, 0777);
                }

                Yii::import('application.vendor.wideImage.WideImage'); // Библиотека управления изображениями

                $sourcePath = pathinfo($name);
                $wideImage = WideImage::load($uploadPath . $name);
                $white = $wideImage->allocateColor(255, 255, 255);

                // тут не учтены не квадратные разрешения
                $wideImage->resize($thumb, $thumb)->resizeCanvas($thumb, $thumb, 'center', 'center', $white)->saveToFile($uploadPath . $thumb . DIRECTORY_SEPARATOR . $sourcePath['filename'] . '.jpg', 75);
            }
        } else {
            $src = Shop::$uploadsUrl . $name;
        }

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
        if (!count($this->photo)) {
            return '';
        }

        if ($index === false) {
            $index = key($this->photo);
        }

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

        $criteria = new CDbCriteria;

        $alias = $this->getTableAlias();

        // фильтрация по промежуткам дат
        if ((isset($this->date_add_from) && trim($this->date_add_from) != "") && (isset($this->date_add_to) && trim($this->date_add_to) != "")) {
            $criteria->addBetweenCondition($alias . '.add_date', '' . date_format(new DateTime($this->date_add_from), 'Y-m-d') . '', '' . date_format(new DateTime($this->date_add_to), 'Y-m-d') . '');
        }

        if ((isset($this->date_edit_from) && trim($this->date_edit_from) != "") && (isset($this->date_edit_to) && trim($this->date_edit_to) != "")) {
            $criteria->addBetweenCondition($alias . '.edit_date', '' . date_format(new DateTime($this->date_edit_from), 'Y-m-d') . '', '' . date_format(new DateTime($this->date_edit_to), 'Y-m-d') . '');
        }

        //$criteria->compare('page_title',$this->page_title,true);
        //$criteria->compare('page_alias',$this->page_alias,true);
        //$criteria->compare('description',$this->description,true);
        //$criteria->compare('waranty',$this->varanty,true);
        //$criteria->compare('price',$this->price);
        if (!empty($this->code)) {
            $criteria->compare($alias . '.code', $this->code, true);
        }

        $criteria->compare($alias . '.product_name', $this->product_name, true);
        $criteria->compare($alias . '.rating', $this->rating, true);
        $criteria->compare($alias . '.status', $this->status);

        // Критерии для фильтрации по related таблицам
        $criteria->compare('user.' . User::FIRST_NAME, $this->user_search, true);
        $criteria->compare('brand.brand_name', $this->brand_search, true);
        $criteria->compare('cat.cat_name', $this->cat_search, true);
        $criteria->compare('supplier.id', $this->supplier_search, true);

        // сортировка по наличию фоток
        if (strpos($_GET['Shop_sort'], 'photo') === 0) {
            $has_photo_sql = "(SELECT LOCATE('\"photo\";a:0:{}', `shop_extra`) FROM `" . $this->tableName() . "` WHERE `id`=t.`id`)";
            $criteria->select = array(
                '*',
                $has_photo_sql . " as has_photo",
            );
        }

        $criteria->with = array(
            'cat' => array('select' => 'cat.cat_name'),
            'brand' => array('select' => 'brand.brand_name'),
            'user' => array('select' => 'user.' . User::FIRST_NAME),
            'supplier',
        );

        return new CActiveDataProvider($this->lastEdited(), array(
            'criteria' => $criteria,
            'sort' => array(
                'attributes' => array(
                    'photo' => array(
                        'asc' => 'has_photo',
                        'desc' => 'has_photo DESC',
                    ),
                    'user_search' => array(
                        'asc' => 'user.' . User::FIRST_NAME,
                        'desc' => 'user.' . User::FIRST_NAME . ' DESC',
                    ),
                    'cat_search' => array(
                        'asc' => 'cat.cat_name',
                        'desc' => 'cat.cat_name DESC',
                    ),
                    'brand_search' => array(
                        'asc' => 'brand.brand_name',
                        'desc' => 'brand.brand_name DESC',
                    ),
                    'supplier_search' => array(
                        'asc' => 'supplier.name',
                        'desc' => 'supplier.name DESC',
                    ),
                    '*',
                ),
            ),
        ));
    }

    public function filter(array $filterData)
    {
        $criteria = new CDbCriteria;
        $alias = $this->getTableAlias();

        // фильтруем по характеристикам
        if (isset($filterData['CF']) || isset($filterData['CNF'])) {
            $this->getCharSubQuery($filterData, $criteria);
        }

        // фильтруем по бренду
        if (isset($filterData['BF'])) {
            $criteria->compare('brand_id', $filterData['BF']);
        }

        // фильтруем по диапазону цены
        if (isset($filterData['Shop'])) {
            $criteria->addBetweenCondition('CAST( ' . $alias . '.price AS DECIMAL )', $filterData['Shop']['priceMin'], $filterData['Shop']['priceMax'], 'AND');
        }

        $dataProvider = new CActiveDataProvider(Shop::model(), array(
            'criteria' => $criteria,
            'sort' => array(
                // определяем сортировку, что бы сверху были товары, которые есть в наличии, а так же самые популярные из них
                'defaultOrder' => $alias . '.`status` ASC, ' . $alias . '.`rating` DESC, ' . $alias . '.`add_date` DESC',
                'attributes' => array(
                    'price' => array(
                        'asc' => $alias . '.`status` ASC, ' . $alias . '.`price` ASC, ' . $alias . '.`rating` DESC, ' . $alias . '.`add_date` DESC',
                        'desc' => $alias . '.`status` ASC, ' . $alias . '.`price` DESC, ' . $alias . '.`rating` DESC, ' . $alias . '.`add_date` DESC',
                    ),
                    'rating' => array(
                        'asc' => $alias . '.`status` ASC, ' . $alias . '.`rating` ASC, ' . $alias . '.`add_date` DESC',
                        'desc' => $alias . '.`status` ASC, ' . $alias . '.`rating` DESC, ' . $alias . '.`add_date` DESC',
                    ),
                ),
            ),
            'pagination' => array(
                'pageSize' => Yii::app()->modules->shop->params['prodPageSize'],
            ),
        ));

        return $dataProvider;
    }

    /**
     * Метод генерирует SELECT запрос, который вернет prod_id товаров,
     * у которых присутствуют все выбранные пользователем характеристики
     * Далее присоединяет этот подзапрос (если он не пустой) к основному запросу $criteria
     *
     * @param array $filterData массив с данными фильтра (элементы CF и CNF)
     * @param CDbCriteria $criteria
     * @access protected
     * @return void
     */
    protected function getCharSubQuery(array $filterData, CDbCriteria $criteria)
    {
        // В этой части скрипта мы сначала напишем подзапрос, который достанет нам id товаров, которые подходят под заданные характеристики
        // выборка делается следующим образом:
        // - сначала мы выбираем все строки, которые содержат нужные нам характеристики, используя OR
        // - потом мы делаем count и groupd by prod_id, что бы получить по строке на товар и колонку, в которой написано сколько характеристик совпало
        // - далее в основном запросе сравниваем count с размером массива $ids, если они равны, значит совпали все характеристики
        // далее я буду продолжать повторять обьяснения алгоритма, так как это весьма запутанная часть скрипта
        $charCriteria = new CDbCriteria;

        if (isset($filterData['CF'])) {
            // для типов множественного выбора (checkbox в админке).
            if (is_array($filterData['CF']['m'])) {
                // тут подразумевается, что пользователь ставя галочки расчитывает получить товары,
                // в которых присутствует не менее одного из выбранных им вариантов
                // (к примеру цвет, размер, диагональ экрана, обьем оперативной памяти и т.д.)
                foreach ($filterData['CF']['m'] as $likeId => $likeArr) {
                    // добавляем в массив в котором хранятся все id характеристик характеристик,
                    // которые обязаны присутствовать у товара
                    $ids[] = $likeId;
                    foreach ($likeArr as $likeValue) {
                        $charCriteria->compare('CONCAT("; ", char.char_value, ";")', '; ' . $likeValue . ';', true, 'OR');
                    }
                }
            }

            // для типов радио и выпадающее меню
            // нам надо, что бы эти условия сравнивались между собой через AND, потому мы добавляем их в $criteria первыми
            foreach ($filterData['CF'] as $id => $value) {
                // выбирая одну из характеристик данного типа пользователю нужно получить товар,
                // в котором обязательно присутствует выбранная им опция (потому мы добавляем id характеристики в массив $ids)
                // В конце концов мы должны получить count($ids) характеристик, которые обязаны присутствовать у товара
                // оператор OR используется потому, что на данном этапе мы выбираем все
                // характеристики из shop_char с помощью подзапроса.
                if ($id == 'm') {
                    continue;
                }
                // пропускаем элемент с характеристиками с множественным выбором
                $charCriteria->compare('char.char_value', $value, false, 'OR');
                $ids[] = $id;
            }
        }

        // для типа число
        if (isset($filterData['CNF'])) {
            foreach ($filterData['CNF'] as $id => $value) {
                // проверяем граничные значения
                // если значения фильтра совпадают с граничными значениями - не фильтруем по текущей характеристике
                $charMinMax = Char::setId($id);
                //T!: Char::setId($id)->minValue использовать, когда данные будут кешироваться
                if ($value[0] == $charMinMax->minValue && $value[1] == $charMinMax->maxValue) {
                    continue;
                }

                $charCriteria->addBetweenCondition('CAST( char.char_value AS DECIMAL )', $value[0], $value[1], 'OR');
                $ids[] = $id;
            }
        }

        $charCriteria->compare('char.char_id', $ids);
        $charCriteria->select = 'prod_id, count(prod_id) AS charCount';
        $charCriteria->group = 'prod_id';

        // генерируем подзапрос из $charCriteria
        $model = Char::model();
        $subQuery = $model->getCommandBuilder()->createFindCommand($model->getTableSchema(), $charCriteria, 'char')->getText();

        // заменяем стандартные параметры :ycp на :yiicp, что бы в общем запросе не было конфликтов
        $subQuery = preg_replace('/:ycp(\d+)/', ':yiicp$1', $subQuery);
        // тоже самое для массива с параметрами и их значениями
        if (count($ids)) {
            array_walk($charCriteria->params, function (&$val, $key) use (&$params) {
                $newKey = preg_replace('/:ycp(\d+)/', ':yiicp$1', $key);
                $params[$newKey] = $val;
            });
            $criteria->params = array_merge($criteria->params, $params);

            $criteria->join = 'LEFT OUTER JOIN (' . $subQuery . ') `char` ON (t.id = char.prod_id)';

            // если это не будет истинной, значит нету товара, у которого совпадают все выбранные пользователем характеристики
            $criteria->addCondition('char.charCount=' . count($ids));
        }
    }
}
