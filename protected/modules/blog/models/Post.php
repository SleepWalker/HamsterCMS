<?php

/**
 * This is the model class for table "blog".
 *
 * The followings are the available columns in table 'blog':
 * @property string $id
 * @property string $user_id
 * @property string $cat_id
 * @property string $image
 * @property string $alias
 * @property string $title
 * @property string $content
 * @property string $tags
 * @property integer $status
 * @property string $edit_date
 * @property string $add_date
 *
 * The followings are the available model relations:
 * @property AuthUser $user
 * @property Categorie $cat
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.ShopController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Post extends CActiveRecord
{
	private $_oldTags;
	
	const STATUS_DRAFT=1;
	const STATUS_PUBLISHED=2;
	const STATUS_ARCHIVED=3;
	
	protected $_statusNames = array(
		self::STATUS_DRAFT => '<span style="color:#с9с9с9;">Черновик</span>',
		self::STATUS_PUBLISHED => '<span style="color:#76B348;">Опубликовано</span>',
		self::STATUS_ARCHIVED => '<span style="color:#FE5050;">Архив</span>',
	);
	
	/**
	 *  Переменные специально для поиска и фильтрации с помощью search()
	 */
	public $user_search; // свойство для реализации фильтрации по имени юзера
	// фильтрация по диапазонам дат
	public $date_add_from;
	public $date_add_to;
	public $date_edit_from;
	public $date_edit_to;
		
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Post the static model class
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
			return 'blog';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('alias, title, content, status, cat_id', 'required'),
			array('status', 'in', 'range'=>array(1,2,3)),
			array('cat_id, user_id', 'numerical'),
			array('user_id', 'length', 'max'=>10),
			array('image', 'length', 'max'=>128),
			array('alias, title', 'length', 'max'=>200),
			array('tags', 'match', 'pattern'=>'/^[a-zA-Zа-яА-Я0-9_\s,\.]+$/u',
				'message'=>'В тегах можно использовать только буквы.'),
			array('tags', 'normalizeTags'),
			array('alias', 'unique'),

			array('title, status, user_search, date_add_from, date_add_to, date_edit_from, date_edit_to, cat_id', 'safe', 'on'=>'search'),
		);
	}

	public function behaviors()
	{
		return array(
			'HIU'=>array(
				'class'=>'HIUBehavior',
				'fileAtt' => 'image',
				'dirName' => 'blog',
				'sizes'=>array(
					'normal' => array(
						'width'=>625,
					),
					'original' => array(
						'width'=>1024,
					),
					'thumb' => array(
						'width' => 176,
						'height' => 176,
						'crop' => true,
					),
				),
			),
			'HRating' => array(
				'class' => 'HRatingBehavior',
			),
			'i18n'=>array(
				'class'=>'Hi18nBehavior',
				'i18nAtts'=>'title, content',
				'moduleId'=>'blog',
			),
/*
			'CTimestampBehavior' => array(
				http://stackoverflow.com/questions/2978473/lost-in-dates-and-timezones
				http://stackoverflow.com/questions/1852223/mysql-keep-server-timezone-or-user-timezone
				'class' => 'zii.behaviors.CTimestampBehavior',
				'createAttribute' => 'create_time_attribute',
				'updateAttribute' => 'update_time_attribute',
				'setUpdateOnCreate' => true,
			),
*/
		);
	}
	
	public function normalizeTags($attribute,$params)
	{
		$this->tags=Tag::array2string(array_unique(Tag::string2array($this->tags)));
	}
	
	public function scopes()
	{
		return array(
			'published'=>array(
				'condition'=>'status = '.self::STATUS_PUBLISHED,
			),
			'latest'=>array(
				'order'=>'add_date DESC',
			),
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
			'user' => array(self::BELONGS_TO, 'User', 'user_id'),
			'cat' => array(self::BELONGS_TO, 'Categorie', 'cat_id'),
		);
	}
	
	/**
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

			return true;
		}
		else
			return false;
	}
	
	protected function afterSave()
	{
		parent::afterSave();
		//При сохранении записи мы хотим также обновить информацию о частоте использования тегов (модель Tag)
		Tag::model()->updateFrequency($this->_oldTags, $this->tags);
		//$this->uImage = $this->image;
	}
	
	protected function afterFind()
	{
		parent::afterFind();
		$this->_oldTags=$this->tags;
		//$this->uImage = $this->image;
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'user_id' => 'User',
			'image' => 'img',
			'alias' => 'Адрес материала',
			'title' => 'Название материала',
			'content' => 'Сообщение',
			'cat_id' => 'Категория',
			'tags' => 'Теги',
			'status' => 'Статус',
			'edit_date' => 'Дата редактирования',
			'add_date' => 'Добавлено',
			'user_search' => 'Добавил',
			'uImage' => 'Изображение материала',
		);
	}
	
	/**
	 * @return array типы полей для форм администрирования модуля
	 */
	public function getFieldTypes()
	{
		return array(
			'i18nlang' => $this->i18n->langField,
			'title' => 'text',
			'alias' => 'translit',
			'status' => array(
				'dropdownlist',
				'items' => $this->statusNames,
			),
			'cat_id' => array(
				'dropdownlist',
				'items' => Categorie::model()->catsList,
				'empty' => '--Выберите категорию--',
			),
			'tags' => 'tags',
			'uImage' => 'file',
			'content' => 'markdown',
		);
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
			self::STATUS_PUBLISHED => 'Опубликовано',
			self::STATUS_ARCHIVED => 'Архив',
		);
	}

	/**
	 * Возвращает дату в красивом формате
	 * @param string $date строка с датой из базы данных
	 */
	public function prettyDate($date)
	{
		return Yii::app()->dateFormatter->formatDateTime($date, 'medium', 'short');
	}

	function getPrettyAddDate()
	{
		return $this->prettyDate($this->add_date);
	}

	/**
	 * Возвращает url страницы материала
	 */
	public function getViewUrl()
	{
		return Yii::app()->createUrl('blog/blog/view', array('id' =>$this->alias));
	}
	
	/**
	 *  @return array теги материала в виде массива
	 */
	public function getTagsArr()
	{
		return Tag::model()->string2array($this->tags);
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
			$criteria->addBetweenCondition('t.edit_date', ''.date_format(new DateTime($this->date_edit_from), 'Y-m-d').'', ''.date_format(new DateTime($this->date_edit_to), 'Y-m-d').'');

		$criteria->compare('title',$this->title,true);
		$criteria->compare('tags',$this->tags,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('cat_id',$this->cat_id);
		
		// Критерии для фильтрации по related таблицам
		$criteria->compare( 'user.' . User::first_name, $this->user_search, true );
		
		$criteria->with=array(
			'user'=>array('select'=>'user.' . User::first_name),
			'cat'=>array('select'=>'cat.name'),
		);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
				'attributes'=>array(
					'user_search'=>array(
						'asc'=>'user.' . User::first_name,
						'desc'=>'user.' . User::first_name . ' DESC',
					),
					'*',
				),
			),
		));
	}
}
