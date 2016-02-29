<?php
/**
 * Admin action class for contest module
 *
 * @package    hamster.modules.sectionvideo.admin.SectionvideoAdminController
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
        $requests = \contest\crud\RequestCrud::findAccepted();

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

    public function actionExportContributionsList()
    {
        $requests = \contest\crud\RequestCrud::findAccepted();

        // sort alphabeticaly by group name or musician name
        usort($requests, function ($one, $two) {
            $name1 = $one->getMainName();
            $name2 = $two->getMainName();
            $name1 = trim($name1);
            $name2 = trim($name2);

            if ($name1 == $name2) {
                return 0;
            }
            return $name1 > $name2 ? 1 : -1;
        });

        $html = $this->renderPartial('export_contributions', [
            'requests' => $requests,
        ], true);

        $mpdf = new \mPDF();
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

        if (\Yii::app()->request->getPost('sendPreview')) {
            echo $this->renderPreview();
            \Yii::app()->end();
        }

        if (\Yii::app()->request->getPost('sendCustom')) {
            $subject = \Yii::app()->request->getPost('subject');
            $message = \Yii::app()->request->getPost('message');
            $toEmail = \Yii::app()->request->getPost('toEmail');

            $emailData = [
                'subject' => $subject,
                'view' => 'custom_email',
                'viewData' => [
                    'message' => (new \CMarkdownParser())->transform($message)
                ],
            ];

            try {
                if (empty($toEmail)) {
                    $requests = $this->getRequestsForMailing([
                        'requestType' => \Yii::app()->request->getPost('requestType', 'any'),
                        'type' => \Yii::app()->request->getPost('type', 'any'),
                    ]);

                    foreach ($requests as $request) {
                        \Yii::app()->getModule('contest')->mailer->notifyMusicians($request, $emailData);
                    }
                } else {
                    $emailData['to'] = $toEmail;

                    \Yii::app()->mail->send($emailData);
                }

                \Yii::app()->user->setFlash('success', 'Письма разосланы!');
            } catch (\Exception $e) {
                \Yii::app()->user->setFlash('error', 'Во время рассылки произошла не предвиденная ошибка: ' . $e->getMessage());
            }
            $this->refresh();
        }

        $this->render('mailing');
    }

    public function renderPreview()
    {
        if (\Yii::app()->request->isPostRequest) {
            $message = \Yii::app()->request->getPost('message');

            $requests = $this->getRequestsForMailing([
                'requestType' => \Yii::app()->request->getPost('requestType', 'any'),
                'type' => \Yii::app()->request->getPost('type', 'any'),
            ]);

            if (count($requests)) {
                $musician = $requests[0]->musicians[0];

                return \Yii::app()->getModule('contest')->mailer->render([
                    'view' => 'custom_email',
                    'viewData' => array_merge(
                        $requests[0]->attributes,
                        $musician->attributes,
                        [
                            'message' => (new \CMarkdownParser())->transform($message)
                        ]
                    ),
                ]);
            } else {
                return 'Не найдены адресаты для отправки';
            }
        }
    }

    /**
     * @return contest\models\Request[]
     */
    private function getRequestsForMailing(array $criteria)
    {
        $requestType = $criteria['requestType'];
        $type = $criteria['type'];

        switch ($requestType) {
            case 'accepted':
                $requests = \contest\crud\RequestCrud::findAccepted();
                break;

            case 'notConfirmed':
                $requests = \contest\crud\RequestCrud::findNotConfirmed();
                break;

            case 'any':
            default:
                $requests = \contest\crud\RequestCrud::findAll();
                break;
        }

        if ($type != 'any' && ($type == \contest\models\Request::TYPE_SOLO || $type == \contest\models\Request::TYPE_GROUP)) {
            $requests = array_filter($requests, function ($request) use ($type) {
                return $request->type == $type;
            });
        }

        return $requests;
    }
}
