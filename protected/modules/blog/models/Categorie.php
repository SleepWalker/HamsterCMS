<?php

/**
 * This is the model class for table "blog_categorie".
 *
 * The followings are the available columns in table 'blog_categorie':
 * @property integer $id
 * @property string $alias
 * @property string $name
 * @property integer $parent
 * @property integer $sindex
 */
class Categorie extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Categorie the static model class
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
		return 'blog_categorie';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('alias, name', 'required'),
			array('parent, sindex', 'numerical', 'integerOnly'=>true),
			array('alias, name', 'length', 'max'=>128),
			array('alias', 'unique'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, alias, name, parent, sindex', 'safe', 'on'=>'search'),
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
			'blog' => array(self::HAS_MANY, 'Blog', 'cat_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'alias' => 'ЧПУ категории',
			'name' => 'Название категории',
			'parent' => 'Cat Parent',
			'sindex' => 'Sort Index',
		);
	}
	
	/**
	 * @return array типы полей для форм администрирования модуля
	 */
	public function getFieldTypes()
	{
		return array(
			'name' => 'text',
			'alias' => 'translit',
		);
	}
	
	/**
	 * Для надежности транслитерируем поле alias
	 */
	function beforeValidate()
	{
	  if(parent::beforeValidate())
    {
      $this->alias = Translit::url($this->alias);
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
    return Yii::app()->createUrl('blog/blog/categorie', array('alias'=>$this->alias));
  }

  /**
	 * @return array для использования в dropDownList
	 */
	public function getCatsList()
	{
	  $list = $this->findAll(array('select'=>'id, name', 'order' => 'name'));
	  foreach($list as $catModel)
	  {
	    $newList[$catModel->id] = $catModel->name;
	  }
	  
	  return $newList;
	}

  /**
   * getCatsMenu возвращает массив меню категорий для CMenu виджета.
   * 
   * @access public
   * @return array Меню категорий
   */
  public function getCatsMenu()
  {
    $models = $this->findAll();
    foreach($models as $model)
    {
      $menu[] = array(
        'label' => $model->name,
        'url' => $model->viewUrl,
      );
    }
    return $menu;
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

		$criteria->compare('id',$this->id);
		$criteria->compare('alias',$this->alias,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('parent',$this->parent);
		$criteria->compare('sindex',$this->sindex);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}
