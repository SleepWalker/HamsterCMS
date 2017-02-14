<?php
namespace contest\components;

use hamster\components\exceptions\InvalidUserInputException;
use contest\models\view\ApplyForm;
use contest\components\Mailer;
use contest\crud\RequestCrud;
use CHttpRequest;

class ContestService
{
    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var RequestCrud
     */
    private $requestCrud;

    public function __construct(Mailer $mailer, RequestCrud $requestCrud)
    {
        $this->mailer = $mailer;
        $this->requestCrud = $requestCrud;
    }

    /**
     * @param  string       $userId
     * @param  ApplyForm    $form
     * @param  CHttpRequest $httpRequest
     *
     * @throws InvalidUserInputException
     * @throws Exception
     */
    public function applyToContest(
        string $userId,
        ApplyForm $form,
        CHttpRequest $httpRequest
    ) {
        $form->load($httpRequest);

        if (!$form->validate()) {
            throw new InvalidUserInputException($form->getModels());
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
    }
}
