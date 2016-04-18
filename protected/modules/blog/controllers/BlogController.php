<?php
/**
 * BlogController class for blog module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    blog.controllers.BlogController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

namespace blog\controllers;

use blog\models\Post;
use blog\models\Categorie;

class BlogController extends \Controller
{
    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public $layout = '//layouts/column2';

    /**
     * @return array action filters
     */
/*    public function filters()
{
return array(
'accessControl', // perform access control for CRUD operations
);
}*/

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
/*public function accessRules()
{
return array(
array('allow',  // allow all users to perform 'index' and 'view' actions
'actions'=>array('index','view', 'rss'),
'users'=>array('*'),
),
array('deny',  // deny all users
'users'=>array('*'),
),
);
}*/

    /**
     * beforeRender инициализируем меню альбомов перед тем как запускать экшен
     *
     * @param mixed $action
     * @access protected
     * @return boolean
     */
    protected function beforeAction($action)
    {
        $this->menu = Categorie::model()->catsMenu;
        return parent::beforeAction($action);
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id)
    {
        $this->render('view', array(
            'model' => $this->loadModel($id),
        ));
    }

    public function getIndexDataProvider()
    {
        $criteria = new \CDbCriteria();

        if (isset($_GET['tag'])) {
            $criteria->addSearchCondition('tags', $_GET['tag']);
        }

        if (isset($_GET['alias'])) {
            $criteria->addSearchCondition('cat.alias', $_GET['alias'], true);
        }

        return new \CActiveDataProvider(
            Post::model()
                ->latest()
                ->published()
                ->with('cat', 'user'),
            [
                /*'pagination'=>array(
                'pageSize'=>Yii::app()->params['postsPerPage'],
                ),*/
                'criteria' => $criteria,
            ]
        );
    }

    /**
     * Lists all models.
     * А так же фильтрует модели по тегам из $_GET['tag']
     */
    public function actionIndex()
    {
        $this->render('index', array(
            'dataProvider' => $this->indexDataProvider,
        ));
    }

    public function actionCategorie($alias)
    {
        $this->render('index', array(
            'dataProvider' => $this->indexDataProvider,
        ));
    }

    /**
     *  Генерирует RSS ленту записей блога
     */
    public function actionRss()
    {
        $this->layout = '//layouts/rss';

        $this->render('rss', array(
            'dataProvider' => $this->indexDataProvider,
        ));
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
    public function loadModel($id)
    {
        $model = Post::model()
            ->published()
            ->with('cat', 'user')
            ->findByAttributes(['alias' => $id]);

        if ($model === null) {
            throw new \CHttpException(404, 'The requested page does not exist.');
        }

        return $model;
    }
}
