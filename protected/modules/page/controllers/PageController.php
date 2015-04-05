<?php
/**
 * Controller class for static page displaying
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

namespace page\controllers;

class PageController extends \Controller
{
    public $defaultAction = 'view';

    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    private $_model;

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
            array('allow', // allow all users to perform 'index' and 'view' actions
                'actions' => array('view', 'error'/*,'view'*/),
                'users' => array('*'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Показует страницу
     */
    public function actionView($id = '')
    {
        if (\Yii::app()->request->requestUri == '/page') {
            // избавляемся от дубля главной страницы
            $this->pageNotFound();
        }

        $model = $this->loadModel(array('full_path' => '/' . $id));

        $view = 'static/' . (empty($id) ? 'index' : $id);
        if ($this->getViewFile($view) === false) {
            if ($model === null) {
                $this->pageNotFound();
            }

            $view = 'index';
        }

        $content = $model ? $model->content : $model;

        $this->bodyCssClasses[2] = 'page' . (empty($id) ? 'Main' : ucfirst($id));

        $this->render($view, array(
            'model' => $model,
            'content' => $content,
        ));
    }

    /**
     * Возвращает содержимое страницы для ajax запросов
     * Этот метод вызывается классом ApiController
     */
    public function api($path)
    {
        $_GET['path'] = implode('/', $path);
        $model = $this->loadModel(array('full_path' => '/' . $_GET['path']));
        if ($model === null) {
            $this->pageNotFound();
        }

        // если $partial = true мы возвращаем строку вьюхи, вместо прямого вывода в браузер
        return $this->renderPartial('index', array(
            'model' => $model,
        ), true, true);
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
    public function loadModel(array $param)
    {
        if ($this->_model === null) {
            $this->_model = \page\models\Page::model()->findByAttributes($param);
        }
        return $this->_model;
    }
}
