<?php

/**
 * This is the model class for table "image".
 *
 * The followings are the available columns in table 'image':
 * @property string $name
 * @property string $source
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.ShopController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Image extends CActiveRecord
{
  
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Image the static model class
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
		return 'image';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('name, source', 'required'),
			array('name', 'length', 'max'=>40),
			array('source', 'length', 'max'=>256),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('name, source', 'safe', 'on'=>'search'),
		);
	}

  public function behaviors()
  {
    return array(
      'HIU'=>array(
        'class'=>'HIUBehavior',
        'fileAtt' => 'name',
        'dirName' => 'imageuploads',
        'fileFieldName' => 'file',
        'sizes'=>array(
          'normal' => array(
            'width'=>625,
          ),
          'full' => array(
            'width'=>1024,
          ),
          'thumb' => array(
            'width' => 150,
            'height' => 150,
            'crop' => true,
          ),
        ),
      ),
    );
  }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'name' => 'Name',
			'source' => 'Uploaded From',
		);
	}
  
  /**
	 * Сохраняем загруженное изображение и заполняем модель оставшимися данными
	 */
	protected function beforeValidate()
	{
	  if(parent::beforeValidate())
    {
      if($this->isNewRecord)
      {
        //uri страницы, с которой происходила загрузка
        $this->source = str_replace($_SERVER['HTTP_HOST'], '',
          substr($_SERVER['HTTP_REFERER'], 7)
        );
      }
      return true;
    }
    else
      return false;
	}
  
  /**
   *  @return HTML код текущей картинки
   */
  public function getHtml()
  {
    return CHtml::link($this->img('normal'), $this->src('full'), array('rel'=>'prettyPhoto'));
  }
  
  /**
   *  Отключает компонент WebLog
   */
  static function turnOffWebLog()
  {
    foreach (Yii::app()->log->routes as $route) 
    {
      if ($route instanceof CWebLogRoute) 
      {
        $route->enabled = false;
      }
    }
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

		$criteria->compare('name',$this->name,true);
		$criteria->compare('source',$this->source,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}
