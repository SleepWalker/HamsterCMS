<?php
/**
 * Admin action class for user module
 *
 * @package    Hamster.modules.user.admin.UserAdminController
 */

use user\models\User;
use user\models\MailingForm;
use \AuthItem as AuthItem;
use \AuthAssignment as AuthAssignment;

class UserAdminController extends \admin\components\HAdminController
{
    public function actions()
    {
        return array(
            'hoauth' => array(
                'class' => 'ext.hoauth.HOAuthAdminAction',
                'route' => 'user/oauth',
            ),
        );
    }
    /**
     * @return меню для табов
     */
    public function tabs()
    {
        $transferCount = ($transferCount = AuthAssignment::model()->transferCount) ? ' (<b style="text-decoration: blink;color:orange;">' . $transferCount . '</b>)' : '';
        return array(
            'index' => 'Пользователи',
            'batchmailing' => 'Рассылки',
            'roles' => 'Роли (группы)',
            'roles/update' => array(
                'name' => 'Редактирование роли',
                'display' => 'whenActive',
            ),
            'roles/create' => array(
                'name' => 'Создать роль',
                'display' => 'roles',
            ),
            'transfer' => 'Ожидающие переноса' . $transferCount,
            'hoauth' => 'Вход через социальные сети',
        );
    }

    /**
     *  Выводит таблицу всех товаров
     */
    public function actionIndex()
    {
        $model = new User('search');

        Yii::app()->clientScript->registerScript('groupEdit', '
      var roles = ' . CJavaScript::encode(AuthItem::getAuthItemsList()) . ';
    var $dd;
    $("body").on("click", ".roleRevoke", function() {
          $.ajax("' . Yii::app()->createUrl('admin/admin/user') . '/revoke", {
            type: "post",
            data: {
              roleId: $(this).parent().data("roleid"),
              userId: $(this).parent().data("userid"),
  },
  context: $(this),
  success: function(data){
    $(this).parent().hide("normal");
  }
  });
  return false;
  });
    $("body").on("click", ".roleAssign", function() {
    // создаем выпадающее меню
    if(!$dd)
    {
      $dd = $("<div>").prop("id", "roleChooser").addClass("contextMenu")
        .on("click", "a", function() {
          var target = $(this).parent().data("target");
          $.ajax("' . Yii::app()->createUrl('admin/admin/user') . '/assign", {
            type: "post",
            data: {
              roleId: $(this).data("roleId"),
              role: $(this).text(),
              userId: target.parents("tr").find("td").eq(0).html(),
  },
  success: function(data){
    var $elem = $(data).hide();
    target.parent().before($elem);
    $elem.show("normal");
  }
  });
          $dd.hide("fast");
      return false;
  });
      $.each(roles, function(roleId, role) {
        $dd.append(
          $("<a href=\'\'>").text(role).data("roleId", roleId)
        );
  });
  $("body").append($dd.hide());

  // прячим меню по клику в любой точке Body
  $("body").click(function() {
    $dd.hide("fast");
  });
  }

  // если меню видимое, сначала скроем его, а потом уже переместим
  if($dd.is(":visible"))
    $dd.hide();

  $dd.css({
    top: $(this).offset().top + $(this).height(),
    left: $(this).offset().left,
  })
  .data("target", $(this))
  .show("fast");


    return false;
  });
          ');

        $this->render('table', [
            'dataProvider' => $model->with('roles')->search(),
            'disableButtons' => true,
            'columns' => [
                'id',
                'fullName',
                [
                    'name' => 'emailWithStatus',
                    'type' => 'raw',
                ],
                [
                    'name' => 'roles',
                    'value' => '$data->getRolesControll()',
                    'type' => 'raw',
                ],
                [
                    'name' => 'last_login',
                    'type' => 'datetime',
                ],
                [
                    'name' => 'date_joined',
                    'type' => 'datetime',
                ],
            ],
        ]);
    }

    /**
     * Присваивает роль пользователю (страница со списком пользователей)
     *
     * @access public
     * @return void
     */
    public function actionAssign()
    {
        AuthItem::model()->am->assign($_POST['roleId'], $_POST['userId']);
        echo '<div class="tagControll" data-roleid="' . $_POST['roleId'] . '" data-userid="' . $_POST['userId'] . '">' . $_POST['role'] . '<a href="" class="icon_delete roleRevoke"></a></div>';
    }

    /**
     * Снимает роль с пользователя (страница со списком пользователей)
     *
     * @access public
     * @return void
     */
    public function actionRevoke()
    {
        AuthItem::model()->am->revoke($_POST['roleId'], $_POST['userId']);
    }

    /**
     * Страница со списком ролей.
     *
     * @access public
     * @return void
     */
    public function actionRoles()
    {
        $model = new AuthItem('search');

        $this->render('table', array(
            'dataProvider' => $model->search(),
            'columns' => array(
                'l10edName',
                [
                    'name' => 'type',
                    'value' => '$data->getTypeLabel()',
                ],
                'description',
                'bizrule',
                'data',
            ),
        ));
    }

    public function actionRolesUpdate()
    {
        if (!empty($this->crudid)) {
            $model = AuthItem::model()->findByPk($this->crudid);
        } else {
            $model = new AuthItem();
        }

        $modelName = \CHtml::modelName($model);
        $postData = \Yii::app()->request->getPost($modelName);
        $ajax = \Yii::app()->request->getPost('ajax');

        // AJAX валидация
        if ($ajax) {
            $model->attributes = $postData;
            echo \CActiveForm::validate($model);
            \Yii::app()->end();
        }

        if ($postData) {
            $model->attributes = $postData;

            $model->save();
        }

        $this->renderForm($model);
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

    public function actionBatchmailing()
    {
        $model = new MailingForm();

        // AJAX валидация
        if (isset($_POST['ajax'])) {
            $model->attributes = $_POST['MailingForm'];
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        if (isset($_POST['MailingForm'])) {
            $model->attributes = $_POST['MailingForm'];

            if ($model->validate()) {
                $userList = AuthAssignment::model()->findAllByRole($model->roles);
                foreach ($userList as &$item) {
                    $item = $item->user;
                }

                $message = new YiiMailMessage;
                $message->from = array($model->from => Yii::app()->params['shortName']);
                $message->subject = $model->subject;
                $message->setBody($model->message, 'text/html');
                $status = Yii::app()->mail->batchSend($message, $userList);

                ob_start();
                print_r($status->failed);
                $failed = ob_get_clean();
                Yii::app()->user->setFlash('info', "Отправленно {$status->sent} писем. Не доставленно: $failed");

                $data = array(
                    'action' => 'renewForm',
                    'content' => '<script> location.reload() </script>',
                );
            } else {
                $data = array(
                    'action' => 'renewForm',
                    'content' => $this->renderPartial('update', array(
                        'model' => $model,
                    ), true, true),
                );
            }

            echo json_encode($data, JSON_HEX_TAG);
            Yii::app()->end();
        }

        $this->render('update', array(
            'model' => $model,
        ));
    }

    public function actionTransfer()
    {
        $model = new AuthAssignment('search');
        $activeProvider = $model->with('user')->search();
        $activeProvider->criteria->compare('t.itemname', 'transfer');

        $this->render('table', array(
            'dataProvider' => $activeProvider,
            'buttons' => array(
                'delete' => array(
                    'url' => 'array("revoke", "id" => $data->primaryKey["userid"])',
                ),
                'ok' => array(
                    'url' => 'array("assign", "id" => $data->primaryKey["userid"])',
                ),
            ),
            'columns' => array(
                'name',
                'email',
                array(
                    'name' => 'Выбраная группа',
                    'value' => '$data->data["chosenRole"]',
                ),
            ),
        ));
    }

    /**
     * Отклонение перемещения пользователя в выбранную им при регистрации роль
     *
     * @access public
     * @return void
     */
    public function actionTransferRevoke()
    {
        $this->actionTransferAssign(false);
    }

    /**
     * Подтверждение перемещения пользователя в выбранную им при регистрации роль
     *
     * @param boolean $assign если true пользователь будет перемещен в выбранную им роль
     * @access public
     * @return void
     */
    public function actionTransferAssign($assign = true)
    {
        $model = AuthAssignment::model()->transfer($this->crudid, $assign);

        if ($assign) {
            Yii::app()->user->setFlash('success', 'Пользователь <b>' . $model->name . '</b> успешно перемещен в группу "' . $model->data['chosenRole'] . '"');
        }

        $this->redirect('/admin/user/transfer');
    }

    protected function getAm()
    {
        return Yii::app()->authManager;
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete()
    {
        if (Yii::app()->request->isPostRequest) {
            // we only allow deletion via POST request
            User::model()->findByPk($this->crudid)->delete();
        } else {
            throw new CHttpException(400, 'Не правильный запрос. Пожалуйста не повторяйте этот запрос еще раз.');
        }

    }
}
