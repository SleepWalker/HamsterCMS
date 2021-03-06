<?php
/**
 * Admin action class for blog module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    blog.controllers.blog.BlogAdminController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

class BlogAdminController extends \admin\components\HAdminController
{
    /**
     * @return меню для табов
     */
    public function tabs()
    {
        return array(
            'index' => 'Все материалы',
            'update' => array(
                'name' => 'Редактирование материала',
                'display' => 'whenActive',
            ),
            'create' => array(
                'name' => 'Добавить материал',
                'display' => 'index',
            ),
            'categorie' => 'Управление категориями',
        );
    }

    /**
     * Создает или редактирует модель
     */
    public function actionUpdate($id = null)
    {
        if ($id) {
            $model = \blog\models\Post::model()->findByPk($id);
        } else {
            $model = new \blog\models\Post();
        }
        $modelName = \CHtml::modelName($model);

        // AJAX валидация
        if (isset($_POST['ajax'])) {
            echo \CActiveForm::validate($model);
            \Yii::app()->end();
        }

        if (isset($_POST[$modelName])) {
            $model->attributes = $_POST[$modelName];

            $model->save();
        }

        $this->renderForm($model);
    }

    /**
     * Перенаправляет обработку запроса на действие Update
     */
    public function actionCreate()
    {
        $this->actionUpdate();
    }

    /**
     *  Выводит таблицу всех товаров
     */
    public function actionIndex()
    {
        $model = new \blog\models\Post('search');
        $model->unsetAttributes();
        if (isset($_GET['Post'])) {
            $model->attributes = $_GET['Post'];
        }

        $tags = \blog\models\Tag::model()->findAll();
        $tagsMenu = array();
        foreach ($tags as $tag) {
            array_push($tagsMenu, $tag->name);
        }
        $this->aside = \CMap::mergeArray($this->aside, array('Теги' => $tagsMenu));

        $this->render('table', array(
            'dataProvider' => $model->latest()->search(),
            'options' => array(
                'filter' => $model,
            ),
            'columns' => array(
                array(
                    'name' => 'image',
                    'value' => '$data->img("thumb")',
                    'type' => 'raw',
                    'filter' => '',
                ),
                'title',
                array(
                    'name' => 'cat_id',
                    'value' => '$data->cat->name',
                    'filter' => \blog\models\Categorie::model()->catsList,
                ),
                array(
                    'name' => 'status',
                    'type' => 'raw',
                    'value' => '$data->statusName',
                    'filter' => \blog\models\Post::getStatusNames(),
                ),
                array(
                    'name' => 'user_search',
                    'value' => '$data->user->first_name',
                ),
                array(
                    'name' => 'add_date',
                    'class' => '\admin\components\grid\DateTimeColumn',
                ),
                array(
                    'name' => 'edit_date',
                    'class' => '\admin\components\grid\DateTimeColumn',
                ),
            ),
        ));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id = null)
    {
        if (\Yii::app()->request->isPostRequest) {
            // we only allow deletion via POST request
            $model = \blog\models\Post::model()->findByPk($id)->delete();
        } else {
            throw new \CHttpException(400, 'Не правильный запрос. Пожалуйста не повторяйте этот запрос еще раз.');
        }

    }

    public function actionCategorie()
    {
        // TODO: убрать кнопку charschema
        $models = \blog\models\Categorie::model()->findAll(array(
            'order' => 'sindex ASC',
        ));
        $this->render('dragndrop', array(
            'models' => $models,
            'actions' => [
                'create' => $this->createUrl('categorieCreate'),
            ],
            'attSindex' => 'sindex',
            'attParent' => 'parent',
            'attId' => 'id',
            'attributes' => array(
                'name',
            ),
        ));
    }

    /**
     * Создает или редактирует категорию
     */
    public function actionCategorieUpdate($id = null)
    {
        if (!empty($id) && $this->action->id == 'categorieupdate') {
            $model = \blog\models\Categorie::model()->findByPk($id);
        } else {
            $model = new \blog\models\Categorie();
        }
        $modelName = \CHtml::modelName($model);

        // AJAX валидация
        if (isset($_POST['ajax'])) {
            echo \CActiveForm::validate($model);
            \Yii::app()->end();
        }

        if (isset($_POST[$modelName])) {
            $model->attributes = $_POST[$modelName];
/*
if ($this->crud == 'create')
{
if (!empty($this->crudid)) // Если задан id, значит это форма добавления подкатегории
$model->parent = $this->crudid;
}
 */
            if (empty($model->parent)) {
                $model->parent = 0;
            }
            // Если родитель пустой, значит это категория верхнего уровня

            $valid = $model->save();
        }

        if (isset($_POST['ajaxSubmit'])) {
            $data = array(
                'action' => 'renewForm',
                'content' => $this->renderPartial('update', array(
                    'model' => $model,
                ), true),
            );

            //обновляем страницу
            if ($valid) {
                $data['content'] .= '<script> location.reload() </script>';
            }

            echo json_encode($data, JSON_HEX_TAG);
            \Yii::app()->end();
        } else {
            $this->renderPartial('update', array(
                'model' => $model,
            ), false, true);
        }

    }
    public function actionCategorieCreate()
    {
        $this->actionCategorieUpdate();
    }

    /**
     * Удаление категории
     */
    public function actionCategorieDelete($id = null)
    {
        if (\Yii::app()->user->checkAccess('admin')) {
            // we only allow deletion via POST request
            $model = \blog\models\Categorie::model()->findByPk($id)->delete();
        } else {
            throw new \CHttpException(400, 'Не правильный запрос. Пожалуйста не повторяйте этот запрос еще раз.');
        }

    }

    /**
     * Меняет родителя категории
     */
    public function actionCategorieSetparent()
    {
    }

    /**
     * Меняет порядок отображения категорий
     */
    public function actionCategorieSetsindex()
    {
        if ($_GET['ajax']) {
            // данные для сортировки
            $sindexOld = $_GET['sindexold']; // старый индекс перемещенного элемента
            $sindexNew = $_GET['sindexnew']; // новый индекс перемеещенного элемента
            $id = $_GET['id'];

            $delta = $sindexOld - $sindexNew;
            $delta = ($delta < 0) ? '-1' : '+1';
            $smin = min($sindexOld, $sindexNew);
            $smax = max($sindexOld, $sindexNew); // throw new CHttpException(400,$smin.' '.$smax);exit();

            if ($delta < 0 && $smin == 0) {
                $smin = 1;
            }
            // предотвращаем ухождение sindex в минуса

            \Yii::app()->db->createCommand()
                      ->update(\blog\models\Categorie::model()->tableName(), array(
                          'sindex' => new \CDbExpression('sindex' . $delta),
                      ), 'sindex>=:smin AND sindex<=:smax', array(':smin' => $smin, ':smax' => $smax));

            \Yii::app()->db->createCommand()
                      ->update(\blog\models\Categorie::model()->tableName(), array(
                          'sindex' => $sindexNew,
                      ), 'id=:id', array(':id' => $id));
        }
    }

    /**
     *  @return array JSON массив с тегами для jQuery UI AutoComplete
     */
    public function actionActags()
    {
        $tag = \blog\models\Tag::model()->string2array($_GET['term']); // работаем только с последним тегом из списка
        $tagsArr = \blog\models\Tag::model()->suggestTags(array_pop($tag));
        array_walk($tagsArr, function (&$value, $index) {
            $value = '"' . $value . '"';
        });
        echo '[' . implode(', ', $tagsArr) . ']';
    }
}
