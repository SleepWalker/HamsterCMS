<?php
/**
 * A service class for sending emails
 */

namespace contest\components;

use ext\hamster\Mailer as HamsterMailer;
use contest\models\Request;
use contest\models\Musician;
use contest\models\ContestId;
use contest\components\RequestRepository;

class Mailer extends \CApplicationComponent
{
    private $mailer;
    private $repository;
    private $adminEmail;

    /**
     * @param HamsterMailer     $mailer
     * @param RequestRepository $repository
     * @param string            $adminEmail
     */
    public function __construct(
        HamsterMailer $mailer,
        RequestRepository $repository,
        $adminEmail
    ) {
        $this->mailer = $mailer;
        $this->repository = $repository;

        if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Bad email format');
        }
        $this->adminEmail = $adminEmail;
    }

    public function notifyMusicians(Request $request, array $options)
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

    private function sendNotification($request, $email, array $options = [])
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
                'fullName' => $request->getMainName(),
            ],
        ], $options));
    }

    /**
     * Renders mailing message as it will be send to user
     * @return string
     */
    public function render(array $params = [])
    {
        if (!isset($params['viewData'])) {
            $params['viewData'] = [];
        }

        return $this->mailer->render($params);
    }

    public function notifyAdmin(array $options)
    {
        if (!empty($this->adminEmail)) {
            return $this->mailer->send(array_merge($options, [
                'to' => $this->adminEmail,
            ]));
        }

        return true;
    }

    /**
     * Отправляет письма с ссылками на подтверждение участия.
     * Письма будут отправлены только участникам, чьи заявки были одобрены
     *
     * @param  ContestId|null $contestId
     */
    public function sendConfirmations(ContestId $contestId = null)
    {
        $requests = $this->repository->findNotConfirmed($contestId);

        foreach ($requests as $request) {
            $this->notifyMusicians($request, [
                'subject' => 'Подтверждение участия в конкурсе «Рок єднає нас» 2016',
                'from' => 'contest@estrocksection.kiev.ua',
                'view' => 'request_confirm',
                'viewData' => function ($request) {
                    // TODO: separate service for confirmation key and url generation
                    // TODO: new ConfirmationEmail($request);
                    $confirmationKey = $request->getConfirmationKey();

                    return [
                        'contestName' => '«Рок єднає нас» 2016',
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
}
