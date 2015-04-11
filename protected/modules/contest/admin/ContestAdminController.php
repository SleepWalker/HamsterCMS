<?php
/**
 * Admin action class for blog module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.sectionvideo.admin.SectionvideoAdminController
 * @copyright  Copyright &copy; 2013 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

class ContestAdminController extends \admin\components\HAdminController
{
    /**
     * @return меню для табов
     */
    public function tabs()
    {
        return [
            '' => 'Все видео',
            'update' => [
                'name' => 'Редактирование видео',
                'display' => 'whenActive',
            ],
            'create' => [
                'name' => 'Добавить видео',
                'display' => 'index',
            ],
        ];
    }

    /**
     *  Выводит таблицу всех товаров
     */
    public function actionIndex()
    {
        $model = new \contest\models\Request('search');
        $model->unsetAttributes();
        $modelName = \CHtml::modelName($model);

        if (($attributes = \Yii::app()->request->getParam($modelName))) {
            $model->attributes = $attributes;
        }

        $this->render('table', array(
            'dataProvider' => $model->search(),
            'options' => array(
                'filter' => $model,
            ),
            'columns' => array(
                'name',
                'type',
                'format',
                array(
                    'name' => 'date_created',
                    'type' => 'datetime',
                    'filter' => $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                        'model' => $model,
                        'attribute' => 'date_created', // TODO: _from
                        'language' => 'ru',
                        'defaultOptions' => array(
                            'showOn' => 'focus',
                            'showOtherMonths' => true,
                            'selectOtherMonths' => true,
                            'changeMonth' => true,
                            'changeYear' => true,
                            'showButtonPanel' => true,
                            'autoSize' => true,
                            'dateFormat' => "yy-mm-dd",
                        ),
                    ), true)
                    .
                    $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                        'model' => $model,
                        'attribute' => 'date_created', // TODO: _to
                        'language' => 'ru',
                    ), true),
                ),
            ),
        ));
    }
}
