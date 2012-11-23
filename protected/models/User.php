<?php

/**
 * This is the model class for table "auth_user".
 *
 * The followings are the available columns in table 'auth_user':
 * @property string $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $password
 * @property integer $is_active
 * @property string $last_login
 * @property string $date_joined
 *
 * The followings are the available model relations:
 * @property ShopRating[] $shopRatings
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.ShopController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class User extends CActiveRecord
{
  public $password1, $password2;

  // константы с алиасами колонок, используемых в выборках других модулей
  // (для случаев, когда проводится интеграция с другими бд)
  // оригинальное_имя = 'имя_в_новой_таблице'
  const first_name = 'first_name';
   
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return User the static model class
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
		return 'auth_user';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('first_name, email', 'required'),
			array('first_name, last_name', 'match', 'pattern'=>'/^[a-zA-Zа-яА-Я0-9_\- ]+$/u', 'message'=>'Поле содержит не допустимые знаки.'),
			array('password1, password2', 'required', 'on'=>'register'),
			array('password2', 'compare', 'compareAttribute'=>'password1', 'strict'=>true, 'on'=>'register'),
			array('password1, password2',  'length', 'min'=>7),
			array('is_active', 'boolean'),
			array('first_name, last_name, password1, password2', 'length', 'max'=>30),
			array('email', 'length', 'max'=>75),
			array('email', 'email'),
			array('email', 'unique'),
			array('password', 'length', 'max'=>128),
			
			array('is_active', 'default', 'value'=>0),
			//array('date_joined','default','value'=>time(), 'on'=>'insert'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, first_name, last_name, email, password, is_active, last_login, date_joined', 'safe', 'on'=>'search'),
		);
	}
	
	public function scopes()
  {
      return array(
          'inactive'=>array(
              'condition'=>'is_active=0',
          ),
      );
  }
	
	/**
	 *  Сохраняем ip юзера
	 *  Обновляем даты
	 */
	protected function beforeSave()
  {
    if(parent::beforeSave())
    {
      if($this->isNewRecord)
        $this->date_joined=$this->last_login=new CDbExpression('NOW()');
      else
        $this->last_login=new CDbExpression('NOW()');
      
     if($this->scenario == 'register')
        $this->password = $this->hashPassword($this->password1);
        
      return true;
    }
    else
      return false;
  }

  /**
   * Если у нас новая запись, 
   * то мы после insert сразу обновляем ее содержимое,
   * что бы получить актуальные даты для хэша 
   * 
   * @access public
   * @return void
   */
  public function afterSave()
  {
    if($this->isNewRecord)
      $this->refresh();
  }

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'shopRatings' => array(self::HAS_MANY, 'ShopRating', 'user_id'),
			'address' => array(self::HAS_MANY, 'OrderAddress', 'user_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'first_name' => 'Имя',
			'last_name' => 'Фамилия',
			'email' => 'Email', // (Например: user@mysite.com)
			'password' => 'Password',
			'is_active' => 'Is Active',
			'last_login' => 'Last Login',
			'date_joined' => 'Date Joined',
			'password1' => 'Пароль',
			'password2' => 'Пароль еще раз',
		);
  }
	
	/**
	*  Аутентификация через модель LoginForm
	**/
	public function login($rememberMe = 1)
  {
    $model=new LoginForm;
    
    $model->user_email = $this->email;
  	$model->user_password = $this->password1;
  	$model->rememberMe = $rememberMe;
    
    return $model->validate() && $model->login();
  }
  
  /**
	*  Возвращает hash для подтверждения на основе $suff
	**/
  protected function confirmationHash($suff)
  {
    return md5($this->salt.$this->password.$this->date_joined.$suff);
  }
  
  /**
	*  Возвращает url для подтверждения для действия $action
	**/
  protected function confirmationUrl($action)
  {
    return Yii::app()->createAbsoluteUrl('site/'.$action,array('h'=>$this->{$action.'Hash'}, 'email'=>$this->email));
  }
  
  /**
	*  Возвращает url для подтверждения email
	**/
	public function getConfirmUrl()
	{
	  return $this->confirmationUrl('confirm');
	}
	
	/**
	*  Возвращает hash для подтверждения email
	**/
	public function getConfirmHash()
	{
	  return $this->confirmationHash('mail');
	}
	
	/**
	*  Возвращает url для подтверждения смены пароля
	**/
	public function getChpassUrl()
	{
	  return $this->confirmationUrl('chpass');
	}
	
	/**
	*  Возвращает hash для подтверждения смены пароля
	**/
	public function getChpassHash()
	{
	  return $this->confirmationHash('psw');
	}
  
  /**
	*  Отправляет письмо пользователю
	**/
	public function mail($view, $subject)
	{
	  $message = new YiiMailMessage;
    $message->view = $view;
     
    //userModel is passed to the view
    $message->setBody(array('user'=>$this), 'text/html');
     
     
    $message->addTo($this->email);
    $message->subject = $subject;
    $message->from = array(Yii::app()->params['noReplyEmail'] => Yii::app()->params['shortName']);
    Yii::app()->mail->send($message);
	}
  
  /**
	*  Отправляет письмо для подтверждения Email адреса
	**/
	public function sendMailConfirm()
	{
	  $this->mail('confirmMail', 'Активация аккаунта на ' . Yii::app()->params['shortName']);
	}
	
	/**
	*  Отправляет письмо для подтверждения Email адреса
	**/
	public function sendChpassMail()
	{
	  $this->mail('changePassword', 'Смена пароля на ' . Yii::app()->params['shortName']);
	}
	
	/**
	*  Возвращает соль для текущего юзера
	**/
	protected function getSalt()
	{
	  list($alg, $iter, $salt, $hash) = explode('$', $this->password);
	  return $salt;
	}
	
	/**
	*  Возвращает модель User по его email
	**/
	public function findByEmail($email = false)
	{
	  if(!$email)
      $email = Yii::app()->user->email;
      
    return $this->findByAttributes(array('email'=>$email));
	} 
	
	/**
	*  Проверка пароля
	**/
	public function validatePassword($password)
  {    
    return $this->hashPassword($password, $this->salt)===$this->password;
  }

  /**
  *   Хэш функция из Django
  **/
  public function hashPassword($password, $salt = false)
  {
    $algorythm = 'pbkdf2_sha256';
    $iterations = 10000;
    if(!$salt)
      $salt = $this->generateRandStr(12);
    $hash = $this->pbkdf2('sha256', $password, $salt, $iterations, false, true);
    $hash = base64_encode($hash);
    return $algorythm . '$' . $iterations . '$' . $salt . '$' . $hash;
  }
  
  /**
  * Implementation of the PBKDF2 key derivation function as described in
  * RFC 2898.
  *
  * @param string $PRF Hash algorithm.
  * @param string $P Password.
  * @param string $S Salt.
  * @param int $c Iteration count.
  * @param mixed $dkLen Derived key length (in octets). If $dkLen is FALSE
  *                     then length will be set to $PRF output length (in
  *                     octets).
  * @param bool $raw_output When set to TRUE, outputs raw binary data. FALSE
  *                         outputs lowercase hexits.
  * @return mixed Derived key or FALSE if $dkLen > (2^32 - 1) * hLen (hLen
  *               denotes the length in octets of $PRF output).
  */
  function pbkdf2($PRF, $P, $S, $c, $dkLen = false, $raw_output = false)
  {
     //default $hLen is $PRF output length
     $hLen = strlen(hash($PRF, '', true));
     if ($dkLen === false) $dkLen = $hLen;
  
     if ($dkLen <= (pow(2, 32) - 1) * $hLen) {
         $DK = '';
  
         //create key
         for ($block = 1; $block <= $dkLen; $block++) {
             //initial hash for this block
             $ib = $h = hash_hmac($PRF, $S.pack('N', $block), $P, true);
  
             //perform block iterations
             for ($i = 1; $i < $c; $i++) {
                 $ib ^= ($h = hash_hmac($PRF, $h, $P, true));
             }
  
             //append iterated block
             $DK .= $ib;
         }
  
         $DK = substr($DK, 0, $dkLen);
         if (!$raw_output) $DK = bin2hex($DK);
  
         return $DK;
  
     //derived key too long
     } else {
         return false;
     }
  }
   
  function generateRandStr($length)
  { 
     /*$randstr = ""; 
     for($i=0; $i<$length; $i++){ 
        $randnum = mt_rand(0,61); 
        if($randnum < 10){ 
           $randstr .= chr($randnum+48); 
        }else if($randnum < 36){ 
           $randstr .= chr($randnum+55); 
        }else{ 
           $randstr .= chr($randnum+61); 
        } 
     }*/
     $randstr = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',5)),0,$length);
     return $randstr; 
  }
  
  /**
	 * @return array типы полей для форм администрирования модуля
	 */
	public function getFieldTypes()
	{
		return array(
		  'first_name' => 'text',
		  'last_name' => 'text',
			'email' => 'text',
			'password1' => 'password',
			'password2' => 'password',
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
		$criteria->compare('first_name',$this->first_name,true);
		$criteria->compare('last_name',$this->last_name,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('is_active',$this->is_active);
		$criteria->compare('last_login',$this->last_login,true);
		$criteria->compare('date_joined',$this->date_joined,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}
