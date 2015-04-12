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
    public $defaultAction = 'list';

    /**
     * @return меню для табов
     */
    public function tabs()
    {
        return [
            'list' => 'Все видео',
            'update' => [
                'name' => 'Редактирование видео',
                'display' => 'whenActive',
            ],
            'create' => [
                'name' => 'Добавить видео',
                'display' => 'list',
            ],
        ];
    }

    /**
     *  Выводит таблицу всех товаров
     */
    public function actionList()
    {
        $model = new \contest\models\Request('search');
        $model->unsetAttributes();
        $modelName = \CHtml::modelName($model);

        if (($attributes = \Yii::app()->request->getParam($modelName))) {
            $model->attributes = $attributes;
        }

        $this->render('table', [
            'dataProvider' => $model->with('musicians', 'compositions')->search(),
            'options' => [
                'filter' => $model,
            ],
            'columns' => [
                'name',
                'type',
                [
                    'name' => 'format',
                    'value' => '$data->getFormatLabel()',
                ],
                [
                    'name' => 'compositions',
                    'type' => 'raw',
                    'filter' => false,
                    'value' => '$this->grid->owner->renderPartial("_composition_grid_cell", [
                        "compositions" => $data->compositions
                    ])',
                ],
                [
                    'name' => 'musicians',
                    'type' => 'raw',
                    'filter' => false,
                    'value' => '$this->grid->owner->renderPartial("_musician_grid_cell", [
                        "musicians" => $data->musicians
                    ])',
                ],
                [
                    'name' => 'demos',
                    'type' => 'raw',
                    'filter' => false,
                    'value' => '"<pre>".\CHtml::encode($data->demos)."</pre>"',
                ],
                [
                    'class' => '\admin\components\grid\DateTimeColumn',
                    'name' => 'date_created',
                ],
            ],
        ]);
    }
}
