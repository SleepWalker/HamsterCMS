<?php
/**
 * A service class for sending emails
 */

namespace contest\components;

class Mailer extends \CApplicationComponent
{
    /**
     * TODO: it should accept only DTO
     * for now it accepts \contest\models\view\Request and \contest\models\Request
     */
    public function notifyMusicians(\CModel $request, array $options)
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

                $success = $success && \Yii::app()->mail->send(array_merge([
                    'to' => $musician->email,
                    'viewData' => $musician->attributes,
                ], $curOptions));
            }
        }

        return $success;
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
        $requests = \contest\crud\RequestCrud::findAccepted();

        foreach ($requests as $request) {
            $this->notifyMusicians($request, [
                'subject' => 'Подтверждение участия в конкурсе «Рок єднає нас» 2015',
                'view' => 'request_confirm',
                'viewData' => function ($musician, $request) {
                    $confirmationKey = $request->getConfirmationKey();

                    return [
                        'fullName' => $musician->getFullName(),
                        'logoSrc' => \Yii::app()->createAbsoluteUrl('/images/logo_email.png'),
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
