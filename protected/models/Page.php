<?php

/**
 * This is the model class for table "page".
 *
 * The followings are the available columns in table 'page':
 * @property int    $id
 * @property string $full_path
 * @property string $title
 * @property string $content
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.ShopController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Page extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Page the static model class
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
		return 'page';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('full_path, title, content', 'required'),
      array('full_path', 'unique'),
			array('full_path, title', 'length', 'max'=>128),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('title, content', 'safe', 'on'=>'search'),
		);
	}

  public function behaviors()
  {
    return array(
      'i18n'=>array(
        'class'=>'Hi18nBehavior',
        'i18nAtts'=>'title, content',
      ),
    );
  }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
		  'id' => 'id',
			'full_path' => 'Адрес страницы',
			'title' => 'Title (заголовок окна браузера)',
			'content' => 'Содержимое',
		);
	}
	
	/**
	 * Для надежности транслитерируем поле cat_alias
	 */
	protected function beforeSave()
	{
	  if(parent::beforeSave())
    {
      $this->full_path = '/' . trim(Translit::url($this->full_path, true), '/');
      return true;
    }

    return false;
	}
	
	/**
	 * @return array типы полей для форм администрирования модуля
	 */
	public function getFieldTypes()
	{
		return array(
      'i18nlang' => $this->i18n->langField,
			'title' => 'text',
			'full_path' => 'translitUrl',
			'content' => 'textarea',
		);
	}
  
  /**
   * Возвращает url страницы
   */
	public function getViewUrl()
  {
    if ($this->full_path == '/') // главная страница
      return '/';
    else
      return Yii::app()->createUrl('page' . $this->full_path);
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

		$criteria->compare('title',$this->title,true);
		$criteria->compare('content',$this->content,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}
