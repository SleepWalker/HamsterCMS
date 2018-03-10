<?php
namespace contest\components;

use hamster\components\exceptions\InvalidUserInputException;
use hamster\models\UserId;
use contest\models\Contest;
use contest\components\Factory;
use contest\components\Mailer;
use contest\crud\RequestCrud;
use contest\models\view\ApplyForm;
use user\components\HWebUser;

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

    /**
     * @var HWebUser
     */
    private $user;

    public function __construct(
        Factory $factory,
        Mailer $mailer,
        RequestCrud $requestCrud,
        HWebUser $user
    ) {
        $this->factory = $factory;
        $this->mailer = $mailer;
        $this->requestCrud = $requestCrud;
        $this->user = $user;
    }

    public function getActiveContest()//: ?Contest
    {
        return $this->factory->getSettings()->getActiveContest();
    }

    /**
     * @param  int $requestId
     * @param  string $confirmationKey
     *
     * @return Request
     */
    public function getRequest(int $requestId, string $confirmationKey = null)//:? Request
    {
        $request = $this->requestCrud->findByPk($requestId);

        if (!$request) {
            throw new \InvalidArgumentException('The request does not exists');
        }

        if (!$request->isValidConfirmationKey($confirmationKey)
            && !$this->user->checkAccess('admin')
        ) {
            throw new \InvalidArgumentException('Invalid confirmation key');
        }

        return $request;
    }

    /**
     * @param ApplyForm $form
     *
     * @throws Exception if can not update record
     */
    public function updateRequest(ApplyForm $form)
    {
        if (!$form->validate()) {
            throw new InvalidUserInputException($form);
        }

        $this->requestCrud->update($form->getRequest());
    }

    /**
     * @param  UserId    $userId
     * @param  ApplyForm $form
     *
     * @throws InvalidUserInputException
     * @throws DomainException in case, when we have no active contests
     * @throws Exception
     */
    public function applyToContest(
        UserId $userId,
        ApplyForm $form
    ) {
        $contest = $this->getActiveContest();

        if (!$contest || !$contest->canApply()) {
            throw new \DomainException('Can not create apply form. No active contests to apply to');
        }

        if (!$form->validate()) {
            throw new InvalidUserInputException($form);
        }

        $form->getRequest()->setAttributes(['contest_id' => $contest->getPrimaryKey()], false);

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

    /**
     * @param  int    $requestId
     * @param  string $confirmationKey
     *
     * @throws InvalidArgumentException
     * @throws DomainException in case, when we can not save the request
     */
    public function confirmRequest(int $requestId, string $confirmationKey = null)
    {
        $request = $this->getRequest($requestId, $confirmationKey);

        if (!$confirmationKey && $this->user->checkAccess('admin')) {
            return;
        }

        if (!$request->isConfirmed()) {
            $request->confirm($confirmationKey);

            if (!$request->save()) {
                throw new \DomainException('Error saving request');
            }
        }
    }
}
