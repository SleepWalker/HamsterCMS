<?php
/**
 * A service class for sending emails
 */

namespace contest\components;

use ext\hamster\Mailer as HamsterMailer;
use contest\models\Request;
use contest\models\Musician;
use contest\models\ContestId;

class Mailer extends \CApplicationComponent
{
    private $mailer;
    private $adminEmail;

    /**
     * @param HamsterMailer     $mailer
     * @param string            $adminEmail
     */
    public function __construct(
        HamsterMailer $mailer,
        string $adminEmail
    ) {
        if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Bad email format');
        }

        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
    }

    public function notifyMusicians(Request $request, array $options = []): bool
    {
        $success = true;

        if (!empty($request->contact_email)) {
            $success = $this->sendNotification(
                $request,
                $request->contact_email,
                $options
            );
        } else {
            foreach ($request->musicians as $musician) {
                if (!empty($musician->email)) {
                    $success = $success && $this->sendNotification(
                        $request,
                        $musician->email,
                        $options
                    );
                }
            }
        }

        return $success;
    }

    private function sendNotification(Request $request, string $email, array $options = []): bool
    {
        if (isset($options['viewData'])) {
            $viewData = $options['viewData'];

            if (is_callable($viewData)) {
                $options['viewData'] = $viewData($request);
            }
        }

        return $this->mailer->send(\CMap::mergeArray([
            'to' => $email,
            'viewData' => [
                'contestName' => $request->contest->title,
                'fullName' => $request->getMainName(),
            ],
        ], $options));
    }

    /**
     * Renders mailing message as it will be send to user
     * @return string
     */
    public function render(array $params = []): string
    {
        if (!isset($params['viewData'])) {
            $params['viewData'] = [];
        }

        return $this->mailer->render($params);
    }

    public function notifyAdmin(Request $request, array $options): bool
    {
        if (!empty($this->adminEmail)) {
            return $this->sendNotification($request, $this->adminEmail, $options);
        }

        return true;
    }

    /**
     * Отправляет письмо с ссылкой на подтверждение участия.
     */
    public function sendConfirmation(Request $request): bool
    {
        return $this->notifyMusicians($request, [
            'subject' => 'Подтверждение участия в конкурсе «Рок єднає нас» 2016',
            'from' => 'contest@estrocksection.kiev.ua',
            'view' => 'request_confirm',
            'viewData' => function ($request) {
                $confirmationKey = $request->getConfirmationKey();

                return [
                    'confirmationUrl' => \Yii::app()->createAbsoluteUrl('contest/contest/confirm', [
                        'id' => $request->primaryKey,
                        'key' => $confirmationKey,
                    ]),
                    'firstComposition' => $request->compositions[0]->getFullName(),
                    'secondComposition' => $request->compositions[1]->getFullName(),
                ];
            },
        ]);
    }
}
