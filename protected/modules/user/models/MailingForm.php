<?php
class MailingForm extends CFormModel
{
  public $roles;
  public $message;
  public $subject;
  public $from;

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
		return array(
      array('roles, message, subject', 'required'),
      array('from', 'email'),
		);
	}

  /**
   * Если поле {@link MailingForm::$from} пустое, присваиваем ему эмейл робота  
   * 
   * @access protected
   * @return void
   */
  protected function beforeValidate()
  {
    if(empty($this->from))
    {
      $this->from = Yii::app()->params['noReplyEmail'];
    }
    return parent::beforeValidate();
  }

  /**
   * Возвращает массив для списка ролей в HTML форме  
   * 
   * @static
   * @access public
   * @return array
   */
  public static function getRolesList()
  {
    $roles = AuthItem::getRolesList();
    // госятм писем не отправишь...
    unset($roles['guest']);
    return $roles;
  }

  public function attributeLabels()
  {
    return array(
      'roles' => 'Роли (группы), которые должны получить письмо',
      'subject' => 'Тема письма',
      'from' => 'Email отправителя',
      'message' => 'Сообщение',
    );
  }

  /**
   * Типы полей для формы отправки писем 
   * 
   * @access public
   * @return array
   */
  public function getFieldTypes()
  {
    return array(
      'roles' => array(
        'type' => 'checkboxlist',
        'items' => self::getRolesList(),
      ),
      'subject' => 'text',
      'from' => array(
        'type' => 'text',
        'hint' => 'Оставьте пустым, что бы письма отправились от емейла робота',
      ),
      'message' => 'textarea',
    );
  }
}
