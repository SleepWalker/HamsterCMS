<?php
/**
 * SocialityController class for blog module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.sociality.controllers.SocialityController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class SocialityController extends Controller
{
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
  }
  
  /**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
      'ajaxOnly + index',
		);
	}

	/**
	 * Lists all models.
   * А так же фильтрует модели по тегам из $_GET['tag']
	 */ 
  public function actionIndex()
  {
    // Отключаем jquery (так как при ajax он уже подключен)
    Yii::app()->clientscript->scriptMap['jquery.js'] = Yii::app()->clientscript->scriptMap['jquery.min.js'] = false; 

    if(isset($_POST['Comment']))
      $cData = $_POST['Comment'];
    else
      $cData = array(
        'model_id' => null,
        'model_pk' => null,
      );

    // добавление нового коммента
    if (isset($cData['comment']))
    {

      $comment = new Comment;
      $comment->attributes = $cData;

      if(isset($_POST['ajax']) && $_POST['ajax']==='HCommentForm')
      {
        echo CActiveForm::validate($comment);
        Yii::app()->end();
      }

      if($comment->save())
      {
        // возвращаем фьюху с новым комментарием
        $this->renderPartial('_comment', array(
          'data' => $comment,
        ));
      }
    }else{
      // вывод комментов и формы добавления комментов
      if(isset($_GET['modelId']) && isset($_GET['modelPk']))
      {
        $cData['model_id'] = $_GET['modelId'];
        $cData['model_pk'] = $_GET['modelPk'];
      }

      $comments = new Comment('search');
      $comments->unsetAttributes();
      $comments->attributes=$cData;

      echo $this->renderPartial('view', array(
        // перед тем, как передать dataProvider затераем аттрибуты имени и емейла юзера (гостя)
        'comments' => $comments->unsetUserData()->search(),
        'newComment' => new Comment,
        'modelId' => $cData['model_id'],
        'modelPk' => $cData['model_pk'],
      ), true, true);
    }
	}
}
