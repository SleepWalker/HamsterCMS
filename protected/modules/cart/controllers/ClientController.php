<?php
/**
 * Контроллер для функции "Перезвоните мне" и "Задать вопрос"
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.cart.controllers.ClientController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class ClientController extends Controller
{
  /**
   * actionRecall  
   * 
   * @param mixed $id 
   * @access public
   * @return void
   */
  public function actionRecall($id = false)
  {
    $form = new RecallForm;
    // ajax валидация
    if(isset($_POST['ajax']))
    {
      echo CActiveForm::validate($form);
      Yii::app()->end();
    }

    if(isset($_POST['RecallForm']))
    {
      $form->attributes = $_POST['RecallForm'];
      if($form->validate())
      {
        User::mailAdmin(array(
          'application.modules.cart.views.client.mail',
          'data' => $id ? Shop::model()->findByPk($id) : false,
          'name' => $form->name,
          'phone' => $form->phone,
        ), 'Запрос звонка');
        $this->renderPartial('success');
        Yii::app()->end();
      }
    }

    $this->renderPartial('form', array(
      'cform' => $form->CForm,
    ), false, true);
  }

  /**
   * actionQuestion  
   * 
   * @param mixed $id 
   * @access public
   * @return void
   */
  public function actionQuestion($id = false)
  {
    $form = new QuestionForm;
    // ajax валидация
    if(isset($_POST['ajax']))
    {
      echo CActiveForm::validate($form);
      Yii::app()->end();
    }

    if(isset($_POST['QuestionForm']))
    {
      $form->attributes = $_POST['QuestionForm'];
      if($form->validate())
      {
        User::mailAdmin(array(
          'application.modules.cart.views.client.mail',
          'data' => $id ? Shop::model()->findByPk($id) : false,
          'name' => $form->name,
          'phone' => $form->phone,
          'email' => $form->email,
          'question' => $form->question,
        ), 'Запрос звонка');
        $this->renderPartial('success');
        Yii::app()->end();
      }
    }

    $this->renderPartial('form', array(
      'cform' => $form->CForm,
    ), false, true);
  }
}
