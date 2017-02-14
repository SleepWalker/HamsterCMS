<?php
namespace contest\components;

use hamster\components\exceptions\InvalidUserInputException;
use hamster\models\UserId;
use contest\models\view\ApplyForm;
use contest\components\Factory;
use contest\components\Mailer;
use contest\crud\RequestCrud;
use CHttpRequest;

class ContestService
{
    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var RequestCrud
     */
    private $requestCrud;

    public function __construct(
        Factory $factory,
        Mailer $mailer,
        RequestCrud $requestCrud
    ) {
        $this->factory = $factory;
        $this->mailer = $mailer;
        $this->requestCrud = $requestCrud;
    }

    /**
     * @param  UserId       $userId
     * @param  CHttpRequest $httpRequest
     *
     * @throws InvalidUserInputException
     * @throws Exception
     *
     * @return ApplyForm
     */
    public function applyToContest(
        UserId $userId,
        CHttpRequest $httpRequest
    ) : ApplyForm
    {
        $form = $this->factory->createApplyForm($httpRequest);

        if (!$form->validate()) {
            throw new InvalidUserInputException($form);
        }

        $request = $this->requestCrud->create($form);

        $this->mailer->notifyMusicians($request, [
            'subject' => 'Заявка на участие в конкурсе',
            'view' => 'user_new_request',
        ]);

        $this->mailer->notifyAdmin([
            'subject' => 'Новая заявка на участие в конкурсе',
            'view' => 'admin_new_request',
        ]);

        return $form;
    }
}
