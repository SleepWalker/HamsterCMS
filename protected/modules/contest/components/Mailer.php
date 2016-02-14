<?php
/**
 * A service class for sending emails
 */

namespace contest\components;

class Mailer extends \CApplicationComponent
{
    public function notifyMusicians(\contest\models\Request $request, array $options)
    {
        $success = true;
        foreach ($request->musicians as $musician) {
            if (!empty($musician->email)) {
                $curOptions = $options;
                if (isset($curOptions['viewData'])) {
                    $viewData = $curOptions['viewData'];

                    if (is_callable($viewData)) {
                        $curOptions['viewData'] = $viewData($musician, $request);
                    }
                }

                $success = $success && \Yii::app()->mail->send(\CMap::mergeArray([
                    'to' => $musician->email,
                    'viewData' => $musician->attributes,
                ], $curOptions));
            }
        }

        return $success;
    }

    /**
     * Renders mailing message as it will be send to user
     * @return string
     */
    public function render(\CModel $musician, array $params = [])
    {
        if (!isset($params['viewData'])) {
            $params['viewData'] = [];
        }

        $params['viewData'] = \CMap::mergeArray(
            $musician->attributes,
            $params['viewData']
        );

        return \Yii::app()->mail->render($params);
    }

    public function notifyAdmin(array $options)
    {
        $adminEmail = $this->getModule()->getAdminEmail();
        if (!empty($adminEmail)) {
            return \Yii::app()->mail->send(array_merge([
                'to' => $adminEmail,
            ], $options));
        }

        return true;
    }

    /**
     * Отправляет письма с ссылками на подтверждение участия.
     * Письма будут отправлены только участникам, чьи заявки были одобрены
     */
    public function sendConfirmations()
    {
        $requests = \contest\crud\RequestCrud::findNotConfirmed();

        foreach ($requests as $request) {
            $this->notifyMusicians($request, [
                'subject' => 'Подтверждение участия в конкурсе «Рок єднає нас» 2015',
                'view' => 'request_confirm',
                'viewData' => function ($musician, $request) {
                    $confirmationKey = $request->getConfirmationKey();

                    return [
                        'fullName' => $musician->getFullName(),
                        'confirmationUrl' => \Yii::app()->createAbsoluteUrl('contest/contest/confirm', [
                            'id' => $request->primaryKey,
                            'key' => $confirmationKey,
                        ]),
                        'firstComposition' => $request->compositions[0]->getFullName(),
                        'secondComposition' => $request->compositions[1]->getFullName(),
                    ];
                },
            ]);

            $request->status = $request::STATUS_WAIT_CONFIRM;
            $request->save();
        }
    }

    private function getModule()
    {
        return \Yii::app()->getModule('contest');
    }
}
