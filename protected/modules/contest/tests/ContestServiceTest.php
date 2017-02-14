<?php
use contest\components\Mailer;
use contest\components\ContestService;
use contest\crud\RequestCrud;
use contest\models\Request;
use contest\models\view\ApplyForm;
use hamster\components\exceptions\InvalidUserInputException;

class ContestServiceTest extends \CTestCase
{
    public function testApplyToContestSuccess()
    {
        $userId = '123';

        $form = $this->createMock(ApplyForm::class);
        $httpRequest = $this->createMock(\CHttpRequest::class);
        $mailer = $this->createMock(Mailer::class);
        $requestCrud = $this->createMock(RequestCrud::class);
        $request = $this->createMock(Request::class);
        $service = new ContestService($mailer, $requestCrud);

        $form->expects($this->once())
            ->method('load')
            ->with($httpRequest);
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

        $service->applyToContest($userId, $form, $httpRequest);
    }

    public function testApplyToContestWithInvalidData()
    {
        $userId = '123';

        $form = $this->createMock(ApplyForm::class);
        $httpRequest = $this->createMock(\CHttpRequest::class);
        $mailer = $this->createMock(Mailer::class);
        $requestCrud = $this->createMock(RequestCrud::class);
        $request = $this->createMock(Request::class);
        $service = new ContestService($mailer, $requestCrud);

        $requestCrud->expects($this->never())
            ->method('create');
        $mailer->expects($this->never())
            ->method('notifyMusicians');
        $mailer->expects($this->never())
            ->method('notifyAdmin');
        $form->expects($this->once())
            ->method('load')
            ->with($httpRequest);
        $form->expects($this->once())
            ->method('validate')
            ->willReturn(false);
        $this->expectException(InvalidUserInputException::class);

        $service->applyToContest($userId, $form, $httpRequest);
    }
}
