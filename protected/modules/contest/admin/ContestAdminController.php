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
            'list' => 'Заявки',
            'export' => 'Экспорт',
            'mailing' => 'Рассылки',
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
            'buttons' => [
                'ok' => [
                    'url' => '["accept", "id" => $data->primaryKey]',
                    'label' => 'Принять',
                    'options' => ['ajax' => true],
                ],
                'delete' => [
                    'url' => '["decline", "id" => $data->primaryKey]',
                    'label' => 'Отклонить',
                    'options' => ['confirmation' => false],
                ],
            ],
            'columns' => [
                'id',
                'name',
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
                    'name' => 'status',
                    'filter' => $model->getStatusesList(),
                    'value' => '$data->getStatusLabel()',
                ],
                [
                    'class' => '\admin\components\grid\DateTimeColumn',
                    'name' => 'date_created',
                ],
            ],
        ]);
    }

    public function actionDecline($id)
    {
        try {
            \contest\crud\RequestCrud::decline($id);
        } catch (\Exception $e) {
            throw new \CHttpException(503, $e->getMessage());
        }
    }

    public function actionAccept($id)
    {
        try {
            \contest\crud\RequestCrud::accept($id);
        } catch (\Exception $e) {
            throw new \CHttpException(503, $e->getMessage());
        }
    }

    public function actionExport()
    {
        $this->render('export');
    }

    public function actionExportRequests()
    {
        $requests = \contest\crud\RequestCrud::findAll();

        $html = $this->renderPartial('export_requests', [
            'requests' => $requests,
        ], true);

        $mpdf = new \mPDF();
        $mpdf->WriteHTML($html);
        $mpdf->Output();
        \Yii::app()->end();
    }

    public function actionExportJury()
    {
        $requests = \contest\crud\RequestCrud::findAll();

        foreach ($requests as $request) {
            $nomination = $request->getNominationLabel();
            $ageCategory = $request->getAgeCategoryLabel();
            if (!isset($lists[$nomination])) {
                $lists[$nomination] = [];
            }
            if (!isset($lists[$nomination][$ageCategory])) {
                $lists[$nomination][$ageCategory] = [];
            }

            array_push($lists[$nomination][$ageCategory], $request);
        }

        $html = $this->renderPartial('export_jury', [
            'lists' => $lists,
            'juries' => [
                'Романчишин Василий',
                'Полтарев Петр',
                'Елена собко',
                'Вырвальский Вадим',
                'Корниенко Вадим',
            ],
        ], true);

        $mpdf = new \mPDF('', 'A4-L');
        $mpdf->WriteHTML($html);
        $mpdf->Output();
        \Yii::app()->end();
    }

    public function actionMailing()
    {
        if (\Yii::app()->request->getPost('sendConfirm')) {
            try {
                \Yii::app()->getModule('contest')->mailer->sendConfirmations();
                \Yii::app()->user->setFlash('success', 'Письма разосланы!');
            } catch (\Exception $e) {
                \Yii::app()->user->setFlash('error', 'Во время рассылки произошла не предвиденная ошибка: ' . $e->getMessage());
            }
            $this->refresh();
        }

        $this->render('mailing');
    }
}
