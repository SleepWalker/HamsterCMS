<?php

/**
 * This is the model class for table "comment".
 *
 * The followings are the available columns in table 'comment':
 * @property string $id
 * @property string $user_id
 * @property string $model_pk
 * @property string $model_id
 * @property string $comment
 * @property string $date
 *
 * The followings are the available model relations:
 * @property AuthUser $user
 * @property CommentUser $commentUser
 */
class Comment extends CActiveRecord
{

  /**
   * @property string $_name имя пользователя (для гостевых комментов) 
   * @see {@link Comment::getName()}
   * @see {@link Comment::setName()} 
   */
  protected $_name;

  /**
   * @property string $email емейл пользователя (для гостевых комментов)  
   */
  protected $_email;

  /**
   * @property mixed $_relatedModel модель, к которой привязаны комментарии 
   *    или пустышка в случае если это статические страницы
   */
  protected $_relatedModel;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Comment the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className)->with('user', 'cUser');
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'comment';
  }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
      array('model_pk, model_id, comment', 'required'),
      array('email, name', 'required', 'on' => 'guest'),
			array('name', 'match', 'pattern'=>'/^[a-zA-Zа-яА-Я0-9_\- ]+$/u', 'message'=>'Поле содержит не допустимые знаки.', 'on' => 'guest'),
			array('user_id', 'length', 'max'=>11),
			array('model_pk', 'length', 'max'=>10),
			array('model_id', 'length', 'max'=>16),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, model_pk, model_id, comment, name, email', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'user' => array(self::BELONGS_TO, 'User', 'user_id'),
      'cUser' => array(self::BELONGS_TO, 'CommentUser', 'id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'user_id' => 'User',
			'model_pk' => 'Model Pk',
			'model_id' => 'Model',
			'comment' => 'Comment',
		);
  }

  public function defaultScope()
  {
    return array(
      'order'=>'date DESC',
    );
  }

  /**
   * Если юзер не залогинен, переключает сценарий модели на guest
   * 
   * @access protected
   * @return void
   */
  protected function afterConstruct()
  {
    if(Yii::app()->user->isGuest)
      $this->scenario = 'guest';

    if(Yii::app()->session['HComment'])
      $this->attributes = Yii::app()->session['HComment'];

    parent::afterConstruct();
  }

  /**
   * Форматирует дату сразу после того, 
   * как модель запонится данными из бд
   * 
   * @access protected
   * @return void
   */
  protected function afterFind()
  {
    parent::afterFind();
    if(!$this->isNewRecord)
    {
      $this->date = Yii::app()->dateFormatter->formatDateTime($this->date, 'short', 'short');
    }
  }

  /**
   * Возвращает модель к которой были прикрепленны комменты или обьект-пустышку 
   * с свойством viewUrl в случае, если это статическая страница
   * 
   * @access public
   * @return void
   */
  public function getModel()
  {
    if(!isset($this->_relatedModel))
    {
      list($moduleId, $modelId) = explode('.', $this->model_id);
      if(empty($modelId))
      {
        $modelId = $moduleId;
        unset($moduleId);
      }

      $modelId = ucfirst($modelId);

      if(isset($moduleId) && file_exists(Yii::getPathOfAlias('application.modules.' . $moduleId . '.models.') . DIRECTORY_SEPARATOR . $modelId . '.php'))
      {
        Yii::import('application.modules.' . $moduleId . '.models.' . $modelId);
        $this->metaData->addRelation('modelRelation', array(
          self::BELONGS_TO, $modelId, 'model_pk'
        ));
        $this->_relatedModel = $this->modelRelation;
      }else{
        $this->_relatedModel = (object) array(
          'viewUrl' => Yii::app()->createUrl('page/index', array('path' => strtolower($modelId))),
        );
      }
    }
    return $this->_relatedModel;
  }

  /**
	 *  Обновляем даты
   *  Добавляем автора комментария
	 */
	protected function beforeValidate()
  {
    if(parent::beforeValidate())
    {
      if($this->scenario == 'guest')
      {
        // создаем модель юзера для предварительной валидации
        $this->cUser = new CommentUser;
        $attributes = array(
          'name' => $this->name,
          'email' => $this->email,
        );

        $this->cUser->attributes =  $attributes;
        // сохраняем юзеру в сессию его имя и фамилию
        Yii::app()->session['HComment'] = $attributes;

        // если модель не проходит валидацию, возвращаем ошибки
        if(!$this->cUser->validate())
        {
          foreach($this->cUser->errors as $attribute => $message)
            $this->addError($attribute, reset($message));

          return false;
        }
      }

      return true;
    }
    else
      return false;
  }

  /**
	 *  Обновляем даты
   *  Добавляем id юзера в зависимости от того гость он или нет
	 */
	protected function beforeSave()
  {
    if(parent::beforeSave())
    {
      if($this->isNewRecord)
      {
        $this->date = new CDbExpression('NOW()');
        $this->user_id = $this->scenario == 'guest' ? new CDbExpression('NULL') : Yii::app()->user->id;
      }

      return true;
    }
    else
      return false;
  }


  /**
   * После сохранения модели, если у нас гостевой сценарий, добавляем гостевого юзера в бд 
   * 
   * @access public
   * @return void
   */
  protected function afterSave()
  {
    parent::afterSave();
    if($this->isNewRecord && $this->scenario == 'guest')
    {
      $this->cUser->id = $this->primaryKey;
      $this->cUser->save();
    }
  }

  /**
   * Обрабатывает необходимые поля функцией CHtml::encode()
   * 
   * @access public
   * @return void
   */
  public function htmlEncode()
  {
    $this->comment = CHtml::encode($this->comment);
  }

  /**
   * Возвращает имя юзера, заполняя его 
   * значениями из связей user или cUser 
   * 
   * @access public
   * @return string
   */
  public function getName()
  {
    if(!$this->isNewRecord && empty($this->_name))
    {
      // инициализируем иминем из связей
      $this->_name = $this->user_id ? $this->user->first_name . ' ' . $this->user->last_name : $this->cUser->name;
    }

    return $this->_name;
  }

  /**
   * Сеттер для имени юзера 
   * 
   * @param string $name 
   * @access public
   * @return void
   */
  public function setName($name)
  {
    $this->_name = $name;
  }

  /**
   * Возвращает email юзера, заполняя его 
   * значениями из связей user или cUser 
   * 
   * @access public
   * @return string
   */
  public function getEmail()
  {
    if(!$this->isNewRecord && empty($this->_email))
    {
      // инициализируем иминем из связи cUser
      $this->_email = $this->user_id ? $this->user->email : $this->cUser->email;
    }

    return $this->_email;
  }

  /**
   * Сеттер для аттрибута email
   * 
   * @param string $email 
   * @access public
   * @return void
   */
  public function setEmail($email)
  {
    $this->_email = $email;
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
    $criteria->with = array('user', 'cUser');

		$criteria->compare('t.id',$this->id);
		$criteria->compare('t.user_id',$this->user_id);
		$criteria->compare('t.model_pk',$this->model_pk);
		$criteria->compare('t.model_id',$this->model_id);
		$criteria->compare('t.comment',$this->comment,true);
		$criteria->compare('cUser.name',$this->name,true);
		$criteria->compare(new CDbExpression('CONCAT(user.first_name, " ", user.last_name)'),$this->name,true, 'OR');

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}
