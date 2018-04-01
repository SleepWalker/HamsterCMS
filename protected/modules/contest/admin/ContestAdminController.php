<?php
/**
 * Admin action class for contest module
 *
 * @package hamster.modules.contest.admin
 */

use contest\models\ContestId;
use contest\models\Settings;
use contest\models\Contest;
use contest\crud\RequestCrud;
use contest\models\Request;

class ContestAdminController extends \admin\components\HAdminController
{
    /**
     * @return меню для табов
     */
    public function tabs(): array
    {
        return [
            'index' => 'Конкурсы',
            'update' => [
                'name' => 'Редактирование конкурса',
                'display' => 'whenActive',
            ],
            'create' => [
                'name' => 'Добавить конкурс',
                'display' => 'index',
            ],
            'settings' => 'Настройки',
            'list' => 'Заявки',
            'mailing' => 'Рассылки',
        ];
    }

    public function actionIndex()
    {
        $model = new Contest('search');
        $model->unsetAttributes();

        $this->render('table', [
            'dataProvider' => $model->search(),
            'buttons' => [
                'update',
                'view' => [
                    'url' => '["list", "id" => $data->primaryKey]',
                    'label' => 'Список заявок',
                    'options' => ['target' => null],
                ],
            ],
            'columns' => [
                [
                    'name' => 'title',
                    'header' => 'Title',
                ],
            ],
        ]);
    }

    public function actionUpdate()
    {
        if ($this->crudid) {
            $model = Contest::model()->findByPk($this->crudid);
        } else {
            $model = new Contest();
        }

        $this->ajaxValidate($model);

        $this->saveIfSubmitted($model);

        $this->renderForm($model);
    }

    public function actionCreate()
    {
        $this->actionUpdate();
    }

    public function actionSettings()
    {
        $model = Settings::getInstance();

        $this->ajaxValidate($model);

        $this->saveIfSubmitted($model);

        $this->renderForm($model);
    }

    /**
     * Autocomplete for contest id
     */
    public function actionAcnextContestId()
    {
        $this->autoCompleteResponse(Contest::model(), 'title', [
            'valueAttribute' => 'primaryKey',
        ]);
    }

    public function actionList($id = null)
    {
        $model = new Request('search');
        $model->unsetAttributes();
        $modelName = \CHtml::modelName($model);

        $model->contest_id = $id;

        if (($attributes = \Yii::app()->request->getParam($modelName))) {
            $model->setAttributes($attributes, false);
        }

        $this->render('table', [
            'dataProvider' => $model->with('musicians', 'compositions')->search(),
            'options' => [
                'filter' => $model,
            ],
            'buttons' => [
                'update' => [
                    'url' => '["/contest/contest/request", "id" => $data->primaryKey]',
                    'options' => [
                        'target' => '_blank',
                    ],
                ],
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
            'batchButtons' => [
                'exportRequests' => [
                    'url' => '["exportRequests", "id" => "' . $id . '"]',
                    'label' => 'Экспортировать заявки',
                    'options' => ['target' => '_blank'],
                ],
                'exportAcceptedRequests' => [
                    'url' => '["exportRequests", "id" => "' . $id . '", "status" => [
                        ' . Request::STATUS_ACCEPTED . ',
                        ' . Request::STATUS_WAIT_CONFIRM . ',
                        ' . Request::STATUS_CONFIRMED . ',
                    ]]',
                    'label' => 'Экспортировать принятые заявки',
                    'options' => ['target' => '_blank'],
                ],
                'exportContributionsList' => [
                    'url' => '["exportContributionsList", "id" => "' . $id . '"]',
                    'label' => 'Список для регистрации взносов',
                    'options' => ['target' => '_blank'],
                ],
                'exportJury' => [
                    'url' => '["exportJury", "id" => "' . $id . '"]',
                    'label' => 'Карточки для жюри',
                    'options' => ['target' => '_blank'],
                ],
                'sendConfirm' => [
                    'url' => '["sendConfirm", "id" => "' . $id . '"]',
                    'label' => 'Разослать письма подтверждения',
                ],
            ],
            'columns' => [
                'id',
                [
                    'name' => 'format',
                    'value' => '$data->getFormatLabel()',
                ],
                [
                    'name' => 'age_category',
                    'value' => '$data->getAgeCategoryLabel()',
                ],
                [
                    'name' => 'compositions',
                    'type' => 'raw',
                    'filter' => false,
                    'value' => '$this->grid->owner->renderPartial("_composition_grid_cell", [
                        "compositions" => $data->compositions,
                    ])',
                ],
                [
                    'name' => 'musicians',
                    'type' => 'raw',
                    'filter' => false,
                    'value' => '$this->grid->owner->renderPartial("_musician_grid_cell", [
                        "request" => $data,
                        "musicians" => $data->musicians,
                    ])',
                ],
                [
                    'name' => 'demos',
                    'type' => 'raw',
                    'filter' => false,
                    'value' => '"<pre style=\"white-space: normal\">".\CHtml::encode($data->demos)."</pre>"',
                    'htmlOptions' => [
                        'style' => 'max-width: 300px; overflow-x: auto;',
                    ],
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
            RequestCrud::decline($id);
        } catch (\Exception $e) {
            throw new \CHttpException(503, $e->getMessage());
        }
    }

    public function actionAccept($id)
    {
        try {
            RequestCrud::accept($id);
        } catch (\Exception $e) {
            throw new \CHttpException(503, $e->getMessage());
        }
    }

    public function actionExportRequests(int $id = null, array $status = null, string $format = 'pdf')
    {
        $availableStatuses = Request::getStatusesList();
        $attributes = [];

        foreach ($status as $key) {
            if (!array_key_exists($key, $availableStatuses)) {
                throw new CHttpException(422, 'Bad status values');
            }
        }

        if (!empty($status)) {
            $attributes['status'] = $status;
        }

        $requests = RequestCrud::findAll(
            $id ? new ContestId((int)$id) : null,
            $attributes
        );

        switch ($format) {
            case 'json':
                $data = [];

                foreach ($requests as $request) {
                    array_push($data, $this->requestToArray($request));
                }

                header('Content-Type: application/json');
                echo json_encode($data);
                break;

            case 'pdf':
            default:
                $html = $this->renderPartial('export_requests', [
                    'requests' => $requests,
                ], true);

                $mpdf = new \mPDF();
                $mpdf->WriteHTML($html);
                $mpdf->Output();
                break;
        }
    }

    private function requestToArray(Request $request) : array
    {
        $item = $request->attributes;

        $item['musicians'] = array_map(function ($musician) {
            return $musician->attributes;
        }, $request->musicians);

        $item['compositions'] = array_map(function ($composition) {
            return $composition->attributes;
        }, $request->compositions);

        return $item;
    }

    public function actionExportJury($id = null)
    {
        $requests = RequestCrud::findAccepted($id ? new ContestId((int)$id) : null);

        $lists = [];
        foreach ($requests as $request) {
            $nomination = $request->getFormatLabel();
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

    public function actionExportContributionsList($id = null)
    {
        $requests = RequestCrud::findAccepted($id ? new ContestId((int)$id) : null);

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

    public function actionSendConfirm($id = null)
    {
        try {
            \Yii::app()->getModule('contest')->mailer->sendConfirmations($id ? new ContestId((int)$id) : null);
            \Yii::app()->user->setFlash('success', 'Письма разосланы!');
        } catch (\Exception $e) {
            \Yii::app()->user->setFlash('error', 'Во время рассылки произошла не предвиденная ошибка: ' . $e->getMessage());
        }

        $this->redirect(['list', 'id' => $id]);
    }

    public function actionMailing()
    {
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
                $requests = RequestCrud::findAccepted();
                break;

            case 'notConfirmed':
                $requests = RequestCrud::findNotConfirmed();
                break;

            case 'any':
            default:
                $requests = RequestCrud::findAll();
                break;
        }

        if ($type != 'any' && ($type == Request::TYPE_SOLO || $type == Request::TYPE_GROUP)) {
            $requests = array_filter($requests, function ($request) use ($type) {
                return $request->type == $type;
            });
        }

        return $requests;
    }
}
