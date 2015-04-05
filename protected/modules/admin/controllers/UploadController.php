<?php
/**
 * UploadController class for admin module
 *
 * Обеспечивает централизованную загрузку файлов для модулей
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.admin.controllers.UploadController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

namespace admin\controllers;

// TODO: доступ только через ajax
class UploadController extends \admin\components\HAdminController
{

    public function filters()
    {
        return array(
            'accessControl',
        );
    }

    public function accessRules()
    {
        return array(
            array('allow',
                'roles' => array('admin'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     *  Метод для загрузки изображений через redactorJS
     *
     *  @note в данный момент метод расчитан на работу с редактором markdown
     *
     *  @source http://redactorjs.com/docs/images/
     */
    public function actionImage($id = false)
    {
        \Image::turnOffWebLog(); // отключили weblog route

        $callback = isset($_GET['callback']) ? $_GET['callback'] : (isset($_POST['callback']) ? $_POST['callback'] : false);

        if ($id) {
            $image = \Image::model()->findByPk($id);
        }
        // TODO редактирование

        if (!isset($image)) {
            $image = new \Image();
        }

        // AJAX валидация
        if (isset($_POST['ajax'])) {
            echo \CActiveForm::validate($image);
            \Yii::app()->end();
        }

        $buttons = array();
        $elements = $callback ? array(
            \CHtml::hiddenField('callback', $callback),
        ) : array();

        if (isset($_FILES['Image'])) {
            $image->attributes = $_POST['Image'];

            $valid = $image->save();
        }
        if ($callback) {
            $buttons = array(
                'callback' => array(
                    'type' => 'button',
                    'label' => 'Вставить в редактор',
                    'attributes' => array(
                        'onclick' => $callback . '(this, \'' . $image->src() . '\'); return false;',
                    ),
                ),
            );
        }

        echo 'Здесь будут поля для alt и title';
        //}

        /*
        if($image->save())
        {
        echo $image->getHtml();
        \Yii::app()->end();
        }
        print_r($image->errors);
         */

        $this->renderForm($image, array(
            'buttons' => $buttons,
            'elements' => $elements,
        ));
    }

    /**
     *  @return JSON массив с информацией о загруженных изоображениях для redactorJS
     */
    public function actionImagesList()
    {
        \Image::turnOffWebLog(); // отключили weblog route

        $images = \Image::model()->findAll();

        foreach ($images as $image) {
            $jsonArray[] = array(
                'thumb' => $image->src('thumb'),
                'image' => $image->src('normal'),
                //'folder' => 'test',
                //'title' => 'test1',
                'full' => $image->src('full'),
            );
        }

        header('Content-type: application/json');
        echo \CJSON::encode($jsonArray);
    }
}
