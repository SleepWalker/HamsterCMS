<?php
/**
 * Admin action class for user module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    Hamster.modules.user.admin.AdminAction
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
 
class AdminAction extends HAdminAction
{

  /**
   * @property CDbAuthManager $am
   */
  public $am;

  public function run()
  {    
    // import the module-level models and components
		$this->module->setImport(array(
			'user.models.*',
			'user.components.*',
    ));

    $this->am=Yii::app()->authManager;
  }
  
  /**
	 * @return меню для табов
	 */
  public function tabs() {
    $transferCount = ($transferCount = AuthAssignment::model()->transferCount) ? ' (<b style="text-decoration: blink;color:orange;">' . $transferCount . '</b>)' : '';
    return array(
      ''  => 'Пользователи',
      'roles' => 'Роли (группы)',
      'roles/update'  => array(
        'name' => 'Редактирование роли',
        'display' => 'whenActive',
      ),
      'roles/create'  => array(
        'name' => 'Создать роль',
        'display' => 'roles',
      ),
      'transfer' => 'Ожидающие переноса'.$transferCount,
    );
  }
  
  /**
   *  Выводит таблицу всех товаров
   */
  public function actionIndex() 
  {
    $this->renderText('test');
  }

  public function actionRoles()
  {
    $model=new AuthItem('search');
	  
		$this->render('table',array(
			'dataProvider'=> $model->search(),
			'columns'=>array(
        'name',
        'type',
        'description',
        'bizrule',
        'data',
        )
      )
    );
  }

  public function actionRolesUpdate()
  {
    if (!empty($this->crudid))
      $model = AuthItem::model()->findByPk($this->crudid);
    else
      $model = new AuthItem;

    // AJAX валидация
		if(isset($_POST['ajax']))
		{
      $model->attributes = $_POST['AuthItem'];
      echo CActiveForm::validate($model);
			Yii::app()->end();
		}

    if(isset($_POST['AuthItem']))
    {
      $model->attributes = $_POST['AuthItem'];

      $saved = $model->save();
    }

		if($_POST['ajaxIframe'] || $_POST['ajaxSubmit'])
    {
      // если модель сохранена и это было действие добавления, переадресовываем на страницу редактирования этого же материала
      if($saved && $this->crud == 'create')
        $data = array(
          'action' => 'redirect',
          'content' => str_replace(array('update', 'create', 'delete'), 'update', $this->actionPath) . $model->primaryKey,
        );
      else
        $data = array(
          'action' => 'renewForm',
          'content' => $this->renderPartial('update',array(
                         'model'=>$model,
                       ), true, true),
        );
      
      echo json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
      Yii::app()->end();
    }
		
		if(!$_POST['ajaxSubmit'])
      $this->render('update',array(
			  'model'=>$model,
		  ));
    
  }
  public function actionRolesCreate()
  {
    $this->actionRolesUpdate();
  }

  public function actionRolesDelete()
  {
    //TODO: переместить в модель
    $this->am->removeAuthItem($this->crudid);
  }

/*
  public function actionTransfer()
  {
    $model=new AuthAssignment('search');
    $activeProvider=$model->search();
	  $activeProvider->criteria->compare('t.itemname', 'transfer');

		$this->render('table',array(
			'dataProvider'=> $activeProvider,
			'columns'=>array(
        'itemname',
        'userid',
        'bizrule',
        'data',
        )
      )
    );
  }
 
 */
  public function actionTransfer()
  {
    $model=new AuthAssignment('search');
    $activeProvider=$model->with('user')->search();
	  $activeProvider->criteria->compare('t.itemname', 'transfer');

		$this->render('table',array(
			'dataProvider'=> $activeProvider,
      'buttons' => array(
        'delete' => array(
          'url'=>'"' . $this->actionPath . 'revoke/" . $data->primaryKey["userid"]',
        ), 
        'ok' => array(
          'url'=>'"' . $this->actionPath . 'assign/" . $data->primaryKey["userid"]',
        ),
      ),
			'columns'=>array(
        'name',
        'email',
        array(
          'name' => 'Выбранная группа',
          'value' => '$data->data["chosenRole"]',
        ),
        )
      )
    );
  }

  public function actionTransferRevoke()
  {
    //TODO: переместить в модель
    $this->am->revoke('transfer', $this->crudid);
  }

  public function actionTransferAssign()
  {
    //TODO: переместить в модель
    $assignment = $this->am->getAuthAssignment('transfer', $this->crudid);
    $authAss = AuthAssignment::model()->findByPk(array('userid' => $this->crudid, 'itemname' => 'transfer'));
    $role = $assignment->data['chosenRole'];
    $this->am->assign($role, $this->crudid);
    $this->am->revoke('transfer', $this->crudid);
    $authAss->user->mail(array('transferSuccess', 'role' => $role), 'Ваша заявка одобрена');
    Yii::app()->user->setFlash('success', 'Пользователь <b>' . $authAss->name . '</b> успешно перемещен в группу "' . $role . '"');
    $this->redirect('/admin/user/transfer');
  }
} 
?>
