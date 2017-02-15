<?php
use contest\components\Mailer;
use contest\components\ContestService;
use contest\components\Factory;
use contest\crud\RequestCrud;
use contest\models\Request;
use contest\models\view\ApplyForm;
use hamster\models\UserId;
use hamster\components\exceptions\InvalidUserInputException;

class ContestServiceTest extends \CTestCase
{
    public function testApplyToContestSuccess()
    {
        $userId = new UserId('123');

        $form = $this->createMock(ApplyForm::class);
        $httpRequest = $this->createMock(\CHttpRequest::class);
        $factory = $this->createMock(Factory::class);
        $mailer = $this->createMock(Mailer::class);
        $requestCrud = $this->createMock(RequestCrud::class);
        $request = $this->createMock(Request::class);
        $service = new ContestService($factory, $mailer, $requestCrud);

        $factory->expects($this->once())
            ->method('createApplyForm')
            ->with($httpRequest)
            ->willReturn($form);
        $form->expects($this->once())
            ->method('validate')
            ->willReturn(true);
        $requestCrud->expects($this->once())
            ->method('create')
            ->with($form)
            ->willReturn($request);
        $mailer->expects($this->once())
            ->method('notifyMusicians')
            ->with($request, [
                'subject' => 'Заявка на участие в конкурсе',
                'view' => 'user_new_request',
            ]);
        $mailer->expects($this->once())
            ->method('notifyAdmin')
            ->with([
                'subject' => 'Новая заявка на участие в конкурсе',
                'view' => 'admin_new_request',
            ]);

        $service->applyToContest($userId, $httpRequest);
    }

    public function testApplyToContestWithInvalidData()
    {
        $userId = new UserId('123');

        $form = $this->createMock(ApplyForm::class);
        $httpRequest = $this->createMock(\CHttpRequest::class);
        $factory = $this->createMock(Factory::class);
        $mailer = $this->createMock(Mailer::class);
        $requestCrud = $this->createMock(RequestCrud::class);
        $request = $this->createMock(Request::class);
        $service = new ContestService($factory, $mailer, $requestCrud);

        $requestCrud->expects($this->never())
            ->method('create');
        $mailer->expects($this->never())
            ->method('notifyMusicians');
        $mailer->expects($this->never())
            ->method('notifyAdmin');
        $factory->expects($this->once())
            ->method('createApplyForm')
            ->with($httpRequest)
            ->willReturn($form);
        $form->expects($this->once())
            ->method('validate')
            ->willReturn(false);

        try {
            $service->applyToContest($userId, $httpRequest);
        } catch (InvalidUserInputException $ex) {
            $this->assertSame($form, $ex->getModel());
        }
    }
}
