<?php

/**
 * This is the model class for table "shop_char_shema".
 *
 * The followings are the available columns in table 'shop_char_shema':
 * @property integer $cat_id
 * @property string $char_shema
 * @property string $type тип характеристики
 * @property integer $sindex sorting index
 * @property string $params serialized params of char
 *
 * The followings are the available model relations:
 * @property ShopCategorie $cat
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.models.CharShema
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class CharShema extends CActiveRecord
{
  // indicates wether current char should be displayed as caption
  public $isCaption;
  // indicates wether current char sould be required
  public $isRequired;
  
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return CharShema the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public $typeList = array(
	 '1' => 'Строка',
	 '2' => 'Выпад. меню',
	 '3' => 'Множеств. выбор',
	 '4' => 'Число',
	 '5' => 'Одиноч. выбор',
	 '6' => 'Скрытое поле',
	);
	
  public $_ddMenuArr; // в этой переменной хранится массив для выпадающей менюшки или чекбоксов (редактирование товара - таблица с характеристиками)

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'shop_char_shema';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('char_name', 'required'),
			array('cat_id, type, sindex', 'numerical', 'integerOnly'=>true),
			array('type', 'default', 'value'=>1, 'setOnEmpty' => true),
			array('char_suff', 'length', 'max'=>2000),
      array('isCaption, isRequired', 'boolean'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('cat_id, char_name', 'safe', 'on'=>'search'),
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
			'cat' => array(self::BELONGS_TO, 'Categorie', 'cat_id'),
			'char' => array(self::HAS_MANY, 'Char', 'char_id'),
		);
	}
  
  /**
	 * Scope
	 * Делает выборку по определенному продукту
	 */
  public function prodId($prodId=false)
  {
    if(!$prodId) 
      return $this;
      
    $this->getDbCriteria()->mergeWith(array(
      'with'=>array(
        'char'=>array(
          //'joinType'=>'LEFT JOIN',
          'condition'=>'char.prod_id=:prodId',
          'params'=>array(':prodId'=>$prodId),
        ),
      ),
      //'order'=>'t.char_name',
    ));
    return $this;
  }
  
  public function defaultScope()
  {
    return array(
      'order'=>'sindex ASC',
    );
  }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
		  'char_id' => 'Char',
			'cat_id' => 'Cat',
			'char_name' => 'Название характеристики',
			'char_suff' => 'Суффикс/Варианты выбора',
			'type' => 'Тип',
		);
	}
	
	/**
	 * Рассериализируем массив с дополнительными параметрами поля
	 */
	public function afterFind() 
	{
	  $params = unserialize($this->params);  
    $this->isCaption = (bool)$params['isCaption'];
    $this->isRequired = (bool)$params['isRequired'];
	}
	
	/**
	 * Сериализируем дополнительные параметры поля перед сохранением
   * Присваеваем самый большой и уикальный sindex новой записи
	 */
	public function beforeSave() 
	{
	  if(parent::beforeSave())
    {
      if(empty($this->sindex))
      {
        // при добавлении новой характеристики 
        // нам необходимо выбрать для нее самый большой и уникальный sindex
			  $sindex = Yii::app()->db->createCommand()
          ->select('MAX(sindex)+1 AS sindex')
          ->from($this->tableName())
          ->queryRow();
        $this->sindex = $sindex['sindex'];
      }
      
      if($this->isCaption)
        $params['isCaption'] = 1;
      if($this->isRequired)
        $params['isRequired'] = 1;
      
      $this->params = serialize($params);
	    return true; 
    }
    else
      return false;
	}
	
	/**
	*  Возвращает название характеристики для CGridView
	**/
	public function gridColName()
	{
	  // Дообавляем суффикс-размерность к названию характеристики (если этот суффикс задан)
    // также добавляем звездочку (обязательность)
	  return $this->char_name.($this->hasSuffix ? ", <b>".$this->char_suff . "</b>": "") . 
          ($this->isRequired ? ' <span class="required">*</span>' : '');
	}
	
  /**
   *  @return html код полей для формы дополнительных характеристик в зависимости от их типа
   **/
	public function gridColValue($value = '')
	{
    $return .= CHtml::hiddenField("Char[" . $this->char_id . "][type]", $this->type) . '<div class="errorMessage" id="Char_' . $this->char_id . '_char_value_em_" style="display:none" />';
    if ($this->type == 1 || $this->type == 4) // input type="text"
      return '<div class="row char_value">' . $return .
             CHtml::textField("Char[" . $this->char_id . "][char_value]", $value) . '</div>';
    else
    { // checkbox or select
      if(!empty($this->ddMenuArr['related']))
        // делаем пометку, что имеем дело с зависимыми характеристиками
        $related = 'relatedChar'; 
        
      switch($this->type)
      {
        case 2: // select
        case 5:
        case 6:
          $element = 'dropDownList';
        break;
        case 3: // checkbox
          $element = 'checkBoxList';
        break;
      }

      return '<div class="row char_value">' . $return.
        CHtml::$element("Char[" . $this->char_id . "][char_value]", explode('; ', $value), 
          $this->ddMenuArr['items'], array(
            "empty" => "--Не выбрано--",
            'class'=>$related,
            'relChar' => $this->ddMenuArr['related']
          )
        ) . '</div>';
    }
	}
	
	public function getDdMenuArr()
	{
	  if(empty($this->_ddMenuArr))
	  {
      $tArr = explode(";", $this->char_suff);
      $i = -1; // индекс для подсчета характеристик-вариантов выбора верхнего уровня
      foreach($tArr as $val)
        if(strpos($val, "}}") === false)
        {
          $this->_ddMenuArr['items'][$val] = $val;
          $i++;
        }
        else
        {
          // добавляем зависимую характеристику
          // убираем {{ и }}
          $val = preg_replace("/[\{\}]/", "", $val);
          $this->_ddMenuArr['relatedArr'][$i] = (strpos($val, "::") === false) ? array($val) : explode("::",$val);
        }
        
      if(!empty($this->_ddMenuArr['relatedArr']))
        $this->_ddMenuArr['related'] = CJSON::encode($this->_ddMenuArr['relatedArr']);
	  }
	  return $this->_ddMenuArr;
	}
  
  /**
   *  @return bool есть ли у этой характеристики дети
   */
  public function getHasChilds()
  {
    return strpos($this->char_suff, "}}");
  }
  
  /**
   *  @return bool есть ли у этой характеристики суффикс
   */
  public function getHasSuffix()
  {
    return (in_array($this->type, array(1, 4)) && $this->char_suff != '');
  }
  
  /**
   *  @return bool видимая ли эта характеристика в таблице характеристик
   */
  public function getIsHidden()
  {
    return ($this->type == 6);
  }
	
	/**
	 * Возвращает все характеристики для текущей категории
	 */
	public function findAllByCat($catId)
	{
	  return $this->findAllByAttributes(array('cat_id'=>$catId));
	}
	
	/**
	 * Рассериализируем поля с характеристиками перед сохранением
	 */
	/*public function afterSave() 
	{
	  $this->afterFind();
	  return true; 
	}*/

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('cat_id',$this->cat_id);
		$criteria->compare('char_name',$this->char_name,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}