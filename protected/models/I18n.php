<?php

/**
 * This is the model class for table "i18n".
 *
 * The followings are the available columns in table 'i18n':
 * @property string $id
 * @property string $hash
 * @property string $field_id
 * @property string $locale
 * @property string $translation
 * @property string $created
 * @property string $modified
 */
class I18n extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return I18n the static model class
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
		return 'i18n';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, hash, field_id, locale, translation', 'required'),
			array('id, locale', 'length', 'max'=>10),
			array('field_id', 'length', 'max'=>32),
			array('translation', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, hash, field_id, locale, translation, created, modified', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'hash' => 'Hash',
			'field_id' => 'Field',
			'locale' => 'Locale',
			'translation' => 'translation String',
			'created' => 'Created',
			'modified' => 'Modified',
		);
	}

  /**
   * Обновляем даты перед сохранением  
   * 
   * @access public
   * @return void
   */
  public function beforeSave() {
    if ($this->isNewRecord)
      $this->created = new CDbExpression('NOW()');

    $this->modified = new CDbExpression('NOW()');

    return parent::beforeSave();
  }

  /**
   * Генерирует ссылку на смену языка страницы
   * 
   * @param string $language необходимый язык страницы (en, ru, ua ...)
   * @static
   * @access public
   * @return string ссылка на страницу на языке $language
   */
  public static function createUrl($language)
  {
    $language = $language == Yii::app()->sourceLanguage ? '' : '/' . $language;
    
    return $language . Yii::app()->request->requestUri;
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
		$criteria->compare('hash',$this->hash,true);
		$criteria->compare('field_id',$this->field_id,true);
		$criteria->compare('locale',$this->locale,true);
		$criteria->compare('translation',$this->translation,true);
		$criteria->compare('created',$this->created,true);
		$criteria->compare('modified',$this->modified,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}
