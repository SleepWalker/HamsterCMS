<?php
/**
 * HRatingBehavior поведение, добавляющее функционал рейтинга в модель
 *
 * @uses CActiveRecordBehavior
 * @package hamster.components.HRatingBehavior
 * @version $id$
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @copyright  Copyright &copy; 2013 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

// TODO: аттрибут-флаг, который включит автоматиеческое создание поля $attribute, если его нету в бд
class HRatingBehavior extends CActiveRecordBehavior
{
  public $attribute = 'rating';

  public function attach($owner)
  {
    parent::attach($owner);

    if(!$this->owner->hasAttribute($this->attribute))
      throw new CException('Не могу найти поле `' . $this->owner->tableName() . '`.`' . $this->attribute . '`. Создайте это поле или задайте правильное имя аттрибуту HRatingBehavior::attribute: ALTER TABLE `' . $this->owner->tableName() . '` ADD `' . $this->attribute . '` DECIMAL( 7, 3 ) UNSIGNED NOT NULL');

    // устанавливаем таблицу для текущего запроса
    Rating::$sessionTable = $this->getRatingTableName();
  }

  /**
   * Добавляет голос за текущую модель
   *
   * @param mixed $value оценка модели
   * @access public
   * @return void
   */
  public function addVote($value)
  {
    if ( Yii::app()->request->isAjaxRequest )
    {
      try {
        if(empty($value) || ($value < 1 || $value > 5))
          throw new CDbException('Нету всех необходимых параметров');
        $value = round($value);

        $ratingModel = new Rating;
        $ratingModel->attributes = array(
          'source_id' => $this->owner->primaryKey,
          'user_id' => Yii::app()->user->id,
          'value' => $value,
        );
        $ratingModel->save();
      }
      catch(CDbException $e)
      {
        echo CJSON::encode( array (
          'status'=>'fail',
          'answer'=>'Вы уже голосовали за этот продукт!',
        ) );
        return;
      }

      if (empty($this->ratingVal))
        $rating = '1.'.$value*100;
      else
        $rating = ($this->votesCount + 1) . '.' . (round(($this->ratingVal + $value) / 2, 2) * 100);

      $this->owner->rating = (float)$rating;
      if($this->owner->save())
        echo CJSON::encode( array (
          'status'=>'success',
          'answer'=>'Спасибо, ваш голос учтен!',
        ) );
    }
  }

  /**
   *  Выводит виджет с рейтингом
   */
  public function ratingWidget($params = array(), $showTotalVotes = false)
  {
    // запрос на изменение рейтинга
    if ( Yii::app()->request->isAjaxRequest && !in_array('callbackUrl', $params) && isset($_GET['val']))
    {
      while(@ob_end_clean());
      $this->addVote($_GET['val']);
      Yii::app()->end();
    }

    $defaults = array(
      'model'=> $this->owner,
    	'attribute' => 'ratingVal', // mark 1...5
    	'readOnly' => false,
      'callbackUrl' => '',
    );

    $params = CMap::mergeArray($defaults, $params);

    Yii::app()->controller->widget('ext.EStarRating', $params);

    if($showTotalVotes)
      echo '<span style="vertical-align: 3px;">(' . $this->votesCount . ')</span>';

    // микроразметка для поисковиков
?>
<span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
<meta itemprop="ratingValue" content="<?php echo $this->ratingVal; ?>">
<meta itemprop="ratingCount" content="<?php echo $this->votesCount; ?>"></span></span>
<?php
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
   * Возвращает название таблицы в которой будет хранится подробная информация о каждом голосе
   *
   * @access public
   * @return void
   */
  public function getRatingTableName()
  {
    return $this->owner->tableName() . '_rating';
  }

  /**
   * Сокращение для получения текущего рейтинга модели
   *
   * @access protected
   * @return void
   */
  protected function getRating()
  {
    return $this->owner->{$this->attribute};
  }
}


/**
 * This is the model class for table "shop_rating".
 *
 * The followings are the available columns in table 'shop_rating':
 * @property string $id
 * @property string $prod_id
 * @property string $user_id
 * @property integer $value
 *
 * The followings are the available model relations:
 * @property User $user
 * @property Shop $prod
 */
class Rating extends CActiveRecord
{
  /**
   * @property string $sessionTable Таблица, которая будет использоваться на протяжении запроса. (эта таблица будет менять свое имя в зависимости от модуля)
   */
  public static $sessionTable;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ShopRating the static model class
	 */
	public static function model($className=__CLASS__)
	{
		try
		{
			return parent::model($className);
		}
		catch(CDbException $e)
		{
			self::createDbTable();
			Yii::app()->controller->refresh();
		}
	}

  public function __construct($scenario='insert')
  {
    try
    {
      parent::__construct($scenario);
    }
    catch(CDbException $e)
    {
      self::createDbTable();
      Yii::app()->controller->refresh();
    }
  }

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return Rating::$sessionTable;
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('source_id, user_id, value', 'required'),
			//array('prod_id, user_id', 'unique'),
			array('source_id, user_id', 'numerical', 'integerOnly'=>true),
			array('value', 'numerical', 'min'=>1, 'max'=>5),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, source_id, user_id, value', 'safe', 'on'=>'search'),
		);
	}


  /**
   * преобразовывает ip юзера из бд из int в строку
   *
   * @access protected
   * @return void
   */
  protected function afterFind()
  {
    parent::afterFind();
    $this->ip=long2ip($this->ip);
  }


  /**
   * преобразовывает ip юзера в int для того, что бы сохранить его бд
   */
  protected function beforeSave()
  {
    if(parent::beforeSave())
    {
      if($this->isNewRecord)
      {
        $this->ip=ip2long(Yii::app()->request->getUserHostAddress());
      }
      else
        $this->ip=ip2long($this->ip);

      return true;
    }
    else
      return false;
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'source_id' => 'Prod',
			'user_id' => 'User',
			'value' => 'Value',
		);
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
		$criteria->compare('source_id',$this->prod_id,true);
		$criteria->compare('user_id',$this->user_id,true);
		$criteria->compare('value',$this->value);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * creates table for holding provider bindings
	 */
	protected static function createDbTable()
	{
		$sql = Rating::getTableSql();
		$sql = strtr($sql, array('{{tableName}}' => Yii::app()->db->tablePrefix . Rating::$sessionTable));
		Yii::app()->db->createCommand($sql)->execute();
	}

  protected static function getTableSql()
  {
    ob_start();
?>
SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `{{tableName}}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `source_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `value` double(2,2) NOT NULL,
  `ip` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `source_id` (`source_id`,`user_id`,`ip`),
  KEY `user_id` (`user_id`),
  KEY `source_id_2` (`source_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SET FOREIGN_KEY_CHECKS=1;
<?php
    return ob_get_clean();
  }
}
