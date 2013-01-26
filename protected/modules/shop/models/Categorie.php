<?php

/**
 * This is the model class for table "shop_categorie".
 *
 * The followings are the available columns in table 'shop_categorie':
 * @property integer $cat_id
 * @property string $cat_alias
 * @property string $cat_name
 * @property string $cat_logo
 * @property integer $cat_parent
 * @property string $cat_harcs_labels
 * @property integer $cat_sindex 
 *
 * The followings are the available model relations:
 * @property Shop[] $shops
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.models.Categorie
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
namespace Shop;
class Categorie extends CActiveRecord
{
  public static $uploadsUrl = '/uploads/shop/categorie/';
  public $uImage;
  
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ShopCategorie the static model class
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
		return 'shop_categorie';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('cat_alias, cat_name, cat_parent', 'required'),
			array('cat_parent', 'numerical', 'integerOnly'=>true),
			array('cat_alias, cat_name, cat_logo', 'length', 'max'=>128),
			array('uImage', 'file',
        'types'=>'jpg, gif, png',
        'maxSize'=>1024 * 1024 * 5, // 5 MB
        'allowEmpty'=>'true',
        'tooLarge'=>'Файл весит больше 5 MB. Пожалуйста, загрузите файл меньшего размера.',
        'safe' => true,
			),
			array('cat_alias', 'unique'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('cat_id, cat_alias, cat_name, cat_logo, cat_parent', 'safe', 'on'=>'search'),
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
			'shop' => array(self::HAS_MANY, 'Shop', 'cat_id'),
			'brands' => array(self::HAS_MANY, 'Brand', 'brand_id','through'=>'shop'),
			'charShema' => array(self::BELONGS_TO, 'CharShema', 'cat_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'cat_id' => 'Cat',
			'cat_alias' => 'ЧПУ категории',
			'cat_name' => 'Название категории',
			'cat_logo' => 'Картинка категории',
			'cat_parent' => 'Cat Parent',
			'cat_sindex' => 'Sort Index',
			'uImage' => 'Изображение категории',
		);
	}
	
	/**
	 * @return array типы полей для форм администрирования модуля
	 */
	public function getFieldTypes()
	{
		return array(
			'cat_name' => 'text',
			'cat_alias' => 'translit',
			'uImage' => 'file',
		);
	}
	
	/**
	 * Для надежности транслитерируем поле cat_alias
	 */
	function beforeValidate()
	{
	  if(parent::beforeValidate())
    {
      $this->cat_alias = Translit::url((empty($this->cat_alias) ? $this->cat_name : $this->cat_alias));
      return true;
    }
    else
      return false;
	}
  
  /**
	 * Присваеваем самый большой и уникальный sindex новой записи
	 */
	public function beforeSave() 
	{
	  if(parent::beforeSave())
    {
      if(empty($this->cat_sindex))
      {
        // при добавлении новой характеристики 
        // нам необходимо выбрать для нее самый большой и уникальный sindex
			  $sindex = Yii::app()->db->createCommand()
          ->select('MAX(cat_sindex)+1 AS cat_sindex')
          ->from($this->tableName())
          ->queryRow();
        $this->cat_sindex = $sindex['cat_sindex'];
      }
	    return true; 
    }
    else
      return false;
	}
	
	/**
   * Возвращает url страницы материала
   */
	public function getViewUrl()
  {
    return Yii::app()->createUrl('shop/shop/categorie', array('alias'=>$this->cat_alias));
  }
  
  /**
   * Возвращает uri загрузки файлов
   */
  public function getUploadsUrl()
  {
    return self::$uploadsUrl;
  }
  
  /**
   *  Возвращает массив со списком соседей
   *  array( cat_id => cat_name )
   *  если $recursive == true возвращает список до самого корня категории
   */
  public function getAllSiblings($catId, $recursive=null)
  {
    // достигли корня дерева. возвращаем либо false либо массив всего дерева
    if ($catId == 0) return $recursive;

    $parentId = $this->findByPk($catId);
    $parentId = $parentId->cat_parent;
    
    if ($recursive)
    {
      if(!is_array($recursive)) $recursive = array();
      $recursive[] = array(
        'id' => $catId,
        'items' => $this->getAllChildsByParent($parentId),
      );
      return $this->getAllSiblings($parentId, $recursive);
    }
    else
	    return $this->getAllChildsByParent($parentId);
  }
  
  /**
   *  wrapper для рекурсивного использования getAllSiblings
   *  так же добавляет к дереву детей исходного catId
   */
  public function getDDTree($catId)
  {
    if ($catId == '') // Категория не задана, возвращаем первый уровень
      return array(array(
        'id' => '',
        'items' => $this->getAllChildsByParent(0),
      ));
    else
      $result = $this->getAllSiblings($catId, true);
    
    // Пробуем добавить детей
    if ($catId != '')
      $children = $this->getAllChildsByParent($catId);
    
    if ($children) // если все удалось, добавляем и их в массив
      array_unshift($result, array(
        'id' => '',
        'items' => $children,
      ));
    return $result;
  }
  
  /**
   *  Возвращает массив со списком дейтей
   *  array( cat_id => cat_name )
   */
  public function getAllChildsByParent($parentId)
  {
    $list = $this->getAllByCatParent((int)$parentId);
    if(!$list) return null;
	  foreach($list as $catModel)
	  {
	    $newList[$catModel->cat_id] = $catModel->cat_name;
	  }
	  
	  return $newList;
  }
  
  /**
   *  Возвращает массив содержащий catid родителей категории с $catId или по $cat_alias
   */
  public function getParentsCatIds($catId, $return = array(), &$cat_alias=false)
  {
    if($cat_alias)
    {
      $parentId = $this->findByAttributes(array('cat_alias'=>$cat_alias));
      $cat_alias = $parentId;
      $return[] = $parentId->cat_id;
    }
    else
      $parentId = $this->findByPk($catId);
    $parentId = $parentId->cat_parent;
    
    if($parentId) $return[] = $parentId;
    else return array_reverse($return); // реверсируем, что бы дерево шло по возрастающей

    return $this->getParentsCatIds($parentId, $return);
  }
  
  
	public function getAllByCatParent($parentId) 
	{
	  return $this->findAllByAttributes(array('cat_parent'=>$parentId), array('order' => 'cat_name'));
	}
	
	/**
   *  Возвращает массив с хлебными крошками родителей текущей категории
   */
	public function getParentBreadcrumbs($catParent = null, $tree=array()) 
	{
	  if(count($tree) == 0) //первый запуск
	    $catParent = $this->cat_parent;
	    
	  $model = $this->findByPk($catParent);
    if ($model)
      $tree[$model->cat_name] = Yii::app()->createUrl('shop/categorie/'.$model->cat_alias);
	  if($model->cat_parent !=0 )
	    $this->getParentBreadcrumbs($model->cat_parent, $tree);
	  
	  return $tree;
	}
	
	/**
   *  Возвращает массив с хлебными крошками родителей текущей категории, включая саму категорию
   */
  public function getBreadcrumbs() 
	{
	  $tree = $this->parentBreadcrumbs;
	  // добавляем текущую категорию (последнюю в хлебных крошках)
	  $tree[$this->cat_name] = Yii::app()->createUrl('shop/categorie/'.$this->cat_alias);
	  
	  return $tree;
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

		$criteria->compare('cat_id',$this->cat_id);
		$criteria->compare('cat_alias',$this->cat_alias,true);
		$criteria->compare('cat_name',$this->cat_name,true);
		$criteria->compare('cat_logo',$this->cat_logo,true);
		$criteria->compare('cat_parent',$this->cat_parent);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}
