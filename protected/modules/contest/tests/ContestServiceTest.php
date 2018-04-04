<?php
use contest\components\Mailer;
use contest\components\ContestService;
use contest\components\Factory;
use contest\crud\RequestCrud;
use contest\models\Settings;
use contest\models\Request;
use contest\models\Contest;
use contest\models\ContestId;
use contest\models\view\ApplyForm;
use user\components\HWebUser;
use hamster\models\UserId;
use hamster\components\exceptions\InvalidUserInputException;

class ContestServiceTest extends \CTestCase
{
    public function testGetActiveContest()
    {
        $factory = $this->createMock(Factory::class);
        $mailer = $this->createMock(Mailer::class);
        $requestCrud = $this->createMock(RequestCrud::class);
        $user = $this->createMock(HWebUser::class);
        $settings = $this->createMock(Settings::class);
        $contest = $this->createMock(Contest::class);
        $service = new ContestService($factory, $mailer, $requestCrud, $user);

        $factory->expects($this->once())
            ->method('getSettings')
            ->willReturn($settings);
        $settings->expects($this->once())
            ->method('getActiveContest')
            ->willReturn($contest);

        $this->assertSame($contest, $service->getActiveContest());
    }

    public function testGetRequest()
    {
        $requestId = 1;
        $confirmationKey = 'foo';
        $factory = $this->createMock(Factory::class);
        $mailer = $this->createMock(Mailer::class);
        $requestCrud = $this->createMock(RequestCrud::class);
        $user = $this->createMock(HWebUser::class);
        $request = $this->createMock(Request::class);

        $service = new ContestService($factory, $mailer, $requestCrud, $user);

        $requestCrud->expects($this->once())
            ->method('findByPk')
            ->with($requestId)
            ->willReturn($request);
        $request->expects($this->once())
            ->method('isValidConfirmationKey')
            ->with($confirmationKey)
            ->willReturn(true);

        $this->assertSame($request, $service->getRequest($requestId, $confirmationKey));
    }

    public function testGetRequestNotFound()
    {
        $requestId = 1;
        $confirmationKey = 'foo';
        $factory = $this->createMock(Factory::class);
        $mailer = $this->createMock(Mailer::class);
        $requestCrud = $this->createMock(RequestCrud::class);
        $user = $this->createMock(HWebUser::class);

        $service = new ContestService($factory, $mailer, $requestCrud, $user);

        $requestCrud->expects($this->once())
            ->method('findByPk')
            ->with($requestId)
            ->willReturn(null);

        try {
            $service->getRequest($requestId, $confirmationKey);
        } catch (\InvalidArgumentException $e) {
            $this->assertSame(
                'The request does not exists',
                $e->getMessage()
            );
        }
    }

    public function testGetRequestWrongKey()
    {
        $requestId = 1;
        $confirmationKey = 'foo';
        $factory = $this->createMock(Factory::class);
        $mailer = $this->createMock(Mailer::class);
        $requestCrud = $this->createMock(RequestCrud::class);
        $user = $this->createMock(HWebUser::class);
        $request = $this->createMock(Request::class);

        $service = new ContestService($factory, $mailer, $requestCrud, $user);

        $requestCrud->expects($this->once())
            ->method('findByPk')
            ->with($requestId)
            ->willReturn($request);
        $request->expects($this->once())
            ->method('isValidConfirmationKey')
            ->with($confirmationKey)
            ->willReturn(false);

        try {
            $service->getRequest($requestId, $confirmationKey);
        } catch (\InvalidArgumentException $e) {
            $this->assertSame(
                'Invalid confirmation key',
                $e->getMessage()
            );
        }
    }

    public function testGetRequestWrongKeyAdmin()
    {
        $requestId = 1;
        $confirmationKey = 'foo';
        $factory = $this->createMock(Factory::class);
        $mailer = $this->createMock(Mailer::class);
        $requestCrud = $this->createMock(RequestCrud::class);
        $user = $this->createMock(HWebUser::class);
        $request = $this->createMock(Request::class);

        $service = new ContestService($factory, $mailer, $requestCrud, $user);

        $requestCrud->expects($this->once())
            ->method('findByPk')
            ->with($requestId)
            ->willReturn($request);
        $request->expects($this->once())
            ->method('isValidConfirmationKey')
            ->with($confirmationKey)
            ->willReturn(false);
        $user->expects($this->once())
            ->method('checkAccess')
            ->with('admin')
            ->willReturn(true);

        $this->assertSame($request, $service->getRequest($requestId, $confirmationKey));
    }

    public function testUpdateRequest()
    {
        $factory = $this->createMock(Factory::class);
        $mailer = $this->createMock(Mailer::class);
        $requestCrud = $this->createMock(RequestCrud::class);
        $user = $this->createMock(HWebUser::class);
        $request = $this->createMock(Request::class);
        $form = $this->createMock(ApplyForm::class);

        $service = new ContestService($factory, $mailer, $requestCrud, $user);

        $form->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $form->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);
        $requestCrud->expects($this->once())
            ->method('update')
            ->with($request);

        $service->updateRequest($form);
    }

    public function testUpdateRequestInvalid()
    {
        $factory = $this->createMock(Factory::class);
        $mailer = $this->createMock(Mailer::class);
        $requestCrud = $this->createMock(RequestCrud::class);
        $user = $this->createMock(HWebUser::class);
        $form = $this->createMock(ApplyForm::class);

        $service = new ContestService($factory, $mailer, $requestCrud, $user);

        $form->expects($this->once())
            ->method('validate')
            ->willReturn(false);

        try {
            $service->updateRequest($form);
        } catch (InvalidUserInputException $e) {
            $this->assertSame($form, $e->getModel());
        }
    }

    public function testApplyToContestSuccess()
    {
        $userId = new UserId('123');
        $contestId = 1;

        $form = $this->createMock(ApplyForm::class);
        $factory = $this->createMock(Factory::class);
        $mailer = $this->createMock(Mailer::class);
        $requestCrud = $this->createMock(RequestCrud::class);
        $user = $this->createMock(HWebUser::class);
        $settings = $this->createMock(Settings::class);
        $contest = $this->createMock(Contest::class);
        $request = $this->createMock(Request::class);
        $service = new ContestService($factory, $mailer, $requestCrud, $user);

        $factory->expects($this->once())
            ->method('getSettings')
            ->willReturn($settings);
        $settings->expects($this->once())
            ->method('getActiveContest')
            ->willReturn($contest);
        $contest->expects($this->once())
            ->method('canApply')
            ->willReturn(true);
        $contest->expects($this->any())
            ->method('getPrimaryKey')
            ->willReturn($contestId);
        $form->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $form->expects($this->any())
            ->method('getRequest')
            ->willReturn($request);
        $request->expects($this->once())
            ->method('setAttributes')
            ->with(['contest_id' => $contestId], false);
        $requestCrud->expects($this->once())
            ->method('create')
            ->with($form)
            ->willReturn($request);
        $mailer->expects($this->once())
            ->method('notifyMusicians')
            ->with($request, [
                'subject' => 'Заявка на участие в конкурсе',
                'view' => 'mail_new_request',
            ]);
        $mailer->expects($this->once())
            ->method('notifyAdmin')
            ->with($request, [
                'subject' => 'Новая заявка на участие в конкурсе',
                'view' => 'mail_new_request',
                'viewData' => [
                    'header' => 'Доброго времени суток, поступила новая заявка на участие в конкурсе\n\n---\n\n',
                ],
            ]);


        $service->applyToContest($userId, $form);
    }

    public function testApplyToContestNoActiveContest()
    {
        $userId = new UserId('123');
        $contestId = 1;

        $form = $this->createMock(ApplyForm::class);
        $factory = $this->createMock(Factory::class);
        $settings = $this->createMock(Settings::class);
        $contest = $this->createMock(Contest::class);
        $mailer = $this->createMock(Mailer::class);
        $requestCrud = $this->createMock(RequestCrud::class);
        $request = $this->createMock(Request::class);
        $user = $this->createMock(HWebUser::class);
        $service = new ContestService($factory, $mailer, $requestCrud, $user);

        $factory->expects($this->once())
            ->method('getSettings')
            ->willReturn($settings);
        $settings->expects($this->once())
            ->method('getActiveContest')
            ->willReturn($contest);
        $contest->expects($this->once())
            ->method('canApply')
            ->willReturn(false);
        $requestCrud->expects($this->never())
            ->method('create');

        try {
            $service->applyToContest($userId, $form);
        } catch (\DomainException $e) {
            $this->assertSame(
                'Can not create apply form. No active contests to apply to',
                $e->getMessage()
            );
        }
    }

    public function testApplyToContestWithInvalidData()
    {
        $userId = new UserId('123');

        $form = $this->createMock(ApplyForm::class);
        $factory = $this->createMock(Factory::class);
        $settings = $this->createMock(Settings::class);
        $contest = $this->createMock(Contest::class);
        $mailer = $this->createMock(Mailer::class);
        $requestCrud = $this->createMock(RequestCrud::class);
        $request = $this->createMock(Request::class);
        $user = $this->createMock(HWebUser::class);
        $service = new ContestService($factory, $mailer, $requestCrud, $user);

        $requestCrud->expects($this->never())
            ->method('create');
        $mailer->expects($this->never())
            ->method('notifyMusicians');
        $mailer->expects($this->never())
            ->method('notifyAdmin');
        $factory->expects($this->once())
            ->method('getSettings')
            ->willReturn($settings);
        $settings->expects($this->once())
            ->method('getActiveContest')
            ->willReturn($contest);
        $contest->expects($this->once())
            ->method('canApply')
            ->willReturn(true);
        $form->expects($this->once())
            ->method('validate')
            ->willReturn(false);

        try {
            $service->applyToContest($userId, $form);
        } catch (InvalidUserInputException $e) {
            $this->assertSame($form, $e->getModel());
        }
    }

    public function testSendConfirmations()
    {
        $contestId = new ContestId(1);

        $factory = $this->createMock(Factory::class);
        $mailer = $this->createMock(Mailer::class);
        $requestCrud = $this->createMock(RequestCrud::class);
        $request = $this->getMockBuilder(Request::class)
            ->setMethods(['save'])
            ->getMock();
        $user = $this->createMock(HWebUser::class);
        $service = new ContestService($factory, $mailer, $requestCrud, $user);

        $requests = [
            $request,
            $request,
        ];

        $requestCrud
            ->expects($this->once())
            ->method('findNotConfirmed')
            ->with($this->identicalTo($contestId))
            ->willReturn($requests);

        $mailer
            ->expects($this->exactly(count($requests)))
            ->method('sendConfirmation')
            ->with($request)
            ->willReturn(true);

        $request->expects($this->exactly(count($requests)))->method('save');

        $service->sendConfirmations($contestId);

        $this->assertEquals(
            Request::STATUS_WAIT_CONFIRM,
            $request->status
        );
    }

    public function testConfirmRequest()
    {
        $requestId = 1;
        $confirmationKey = 'foo';
        $factory = $this->createMock(Factory::class);
        $mailer = $this->createMock(Mailer::class);
        $requestCrud = $this->createMock(RequestCrud::class);
        $user = $this->createMock(HWebUser::class);
        $request = $this->createMock(Request::class);

        $service = new ContestService($factory, $mailer, $requestCrud, $user);

        $requestCrud->expects($this->once())
            ->method('findByPk')
            ->with($requestId)
            ->willReturn($request);
        $request->expects($this->any())
            ->method('isValidConfirmationKey')
            ->with($confirmationKey)
            ->willReturn(true);
        $request->expects($this->once())
            ->method('isConfirmed')
            ->willReturn(false);
        $request->expects($this->once())
            ->method('confirm')
            ->with($confirmationKey);
        $request->expects($this->once())
            ->method('save')
            ->willReturn(true);

        $service->confirmRequest($requestId, $confirmationKey);
    }

    public function testConfirmRequestWasConfirmed()
    {
        $requestId = 1;
        $confirmationKey = 'foo';
        $factory = $this->createMock(Factory::class);
        $mailer = $this->createMock(Mailer::class);
        $requestCrud = $this->createMock(RequestCrud::class);
        $user = $this->createMock(HWebUser::class);
        $request = $this->createMock(Request::class);

        $service = new ContestService($factory, $mailer, $requestCrud, $user);

        $requestCrud->expects($this->once())
            ->method('findByPk')
            ->with($requestId)
            ->willReturn($request);
        $request->expects($this->any())
            ->method('isValidConfirmationKey')
            ->with($confirmationKey)
            ->willReturn(true);
        $request->expects($this->once())
            ->method('isConfirmed')
            ->willReturn(true);
        $request->expects($this->never())
            ->method('confirm');
        $request->expects($this->never())
            ->method('save');

        $service->confirmRequest($requestId, $confirmationKey);
    }

    public function testConfirmRequestFailedSave()
    {
        $requestId = 1;
        $confirmationKey = 'foo';
        $factory = $this->createMock(Factory::class);
        $mailer = $this->createMock(Mailer::class);
        $requestCrud = $this->createMock(RequestCrud::class);
        $user = $this->createMock(HWebUser::class);
        $request = $this->createMock(Request::class);

        $service = new ContestService($factory, $mailer, $requestCrud, $user);

        $requestCrud->expects($this->once())
            ->method('findByPk')
            ->with($requestId)
            ->willReturn($request);
        $request->expects($this->any())
            ->method('isValidConfirmationKey')
            ->with($confirmationKey)
            ->willReturn(true);
        $request->expects($this->once())
            ->method('isConfirmed')
            ->willReturn(false);
        $request->expects($this->once())
            ->method('confirm')
            ->with($confirmationKey);
        $request->expects($this->once())
            ->method('save')
            ->willReturn(false);

        try {
            $service->confirmRequest($requestId, $confirmationKey);
        } catch (\DomainException $e) {
            $this->assertSame($e->getMessage(), 'Error saving request');
        }
    }

    public function testConfirmRequestNoKeyAdmin()
    {
        $requestId = 1;
        $confirmationKey = 'foo';
        $factory = $this->createMock(Factory::class);
        $mailer = $this->createMock(Mailer::class);
        $requestCrud = $this->createMock(RequestCrud::class);
        $user = $this->createMock(HWebUser::class);
        $request = $this->createMock(Request::class);

        $service = new ContestService($factory, $mailer, $requestCrud, $user);

        $requestCrud->expects($this->once())
            ->method('findByPk')
            ->with($requestId)
            ->willReturn($request);
        $user->expects($this->any())
            ->method('checkAccess')
            ->with('admin')
            ->willReturn(true);
        $request->expects($this->never())
            ->method('isConfirmed');
        $request->expects($this->never())
            ->method('confirm');
        $request->expects($this->never())
            ->method('save');

        $service->confirmRequest($requestId);
    }
}
