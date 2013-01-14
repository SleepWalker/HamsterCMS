<?php

/**
 * QuestionForm class.
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.cart.models.QuestionForm
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class QuestionForm extends CFormModel
{
	public $name;
	public $phone;
  public $email;
	public $question;

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			// name, email, subject and body are required
			array('name, question', 'required'),
      array('email', 'email'),
      array('phone', 'safe'),
      array('phone', 'contactValidator'),
		);
	}
  
  public function contactValidator($attribute, $params)
  {
    if(empty($this->email) && empty($this->phone))
      $this->addError($attribute, 'Пожалуйста, укажите телефон или электронную почту');
  }

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
  {
    return array(
      'name'=>'Как как вам обращяться?',
      'phone'=>'Ваш телефон',
      'question' => 'Ваш вопрос',
    );
  }

  public function getCForm()
  {
    $id = 'submit'.uniqid();
    return new CForm(array(
      'buttons'=>array(
        'submit'=>array(
          'type'=>'submit',
          'label'=>'Отправить запрос',
          'attributes' => array(
            'class' => 'submit',
            'id' => $id,
            'ajax' => array(
              'url' => '', // в таком случае url указывает на экшен формы
              'success' => 'js:function(html){$("#'.$id.'").parents("div.hForm").replaceWith(html)}',
              'type' => 'post',
              'data' => 'js:$("#'.$id.'").parents("form").serialize()',
            ),
          ),
        )
      ),
      'activeForm'=>array(
        'enableAjaxValidation'=>true,
        'enableClientValidation'=>false,
      ),
      'model' => $this,
      'elements' => array(
        'name' => array(
          'type' => 'text',
        ),
        'phone' => array(
          'type' => 'text',
        ),
        'email' => array(
          'type' => 'email',
        ),
        'question' => array(
          'type' => 'textarea',
        ),
      ),
    ));
  }
}
