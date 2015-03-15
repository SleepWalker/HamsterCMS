<?php

/**
 * ContactForm class.
 * ContactForm is the data structure for keeping
 * contact form data. It is used by the 'contact' action of 'SiteController'.
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.ShopController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class ContactForm extends CFormModel
{
    /**
     * Храним данные в моделе, а не в контроллере (с)Мастир
     */
    public $files = array();
    public $verifyCode;

    public $name;
    public $email;
    public $subject;
    public $body;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            // name, email, subject and body are required
            array('name, email, body, verifyCode', 'required'),
            // email has to be a valid email address
            array('email', 'email'),
            array('body', 'length', 'max' => 3000),
            array('subject', 'length', 'max' => 200),
            // verifyCode needs to be entered correctly
            array('verifyCode', 'captcha', 'allowEmpty' => !CCaptcha::checkRequirements()),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        return array(
            'subject' => 'Тема',
            'body' => 'Сообщение',
            'name' => 'Имя',
            'verifyCode' => 'Проверочный код',
        );
    }

    /**
     * Тема письма
     */
    public function getSubject()
    {
        return $this->subject . '[Обратная связь ' . Yii::app()->params['shortName'] . ']';
    }

    /**
     * Прикрепленные файлы
     */
    public function getFiles()
    {
        return (array) $this->files;
    }
}
