<?php
use contest\components\Mailer;
use contest\models\Request;
use contest\models\Contest;
use contest\models\Composition;
use contest\models\ContestId;
use contest\components\RequestRepository;
use ext\hamster\Mailer as HamsterMailer;

class MailerTest extends \CTestCase
{
    private $mailer;
    private $hmailer;
    private $repository;

    public function setUp()
    {
        $this->hmailer = $this->getMockBuilder(HamsterMailer::class)->getMock();
        $this->repository = $this->getMockBuilder(RequestRepository::class)->getMock();

        $this->mailer = new Mailer($this->hmailer, $this->repository, 'admin@foo.bar');
    }

    public function testSuccessNotifyMusicians()
    {
        $request = new Request();
        $request->attributes = [
            'contact_name' => 'foo',
            'contact_email' => 'foo@bar.com',
        ];
        $request->contest = new Contest();
        $request->contest->title = 'Test contest';

        $expected = true;

        $this->hmailer
            ->expects($this->once())
            ->method('send')
            ->with($this->equalTo([
                'to' => $request->contact_email,
                'viewData' => [
                    'fullName' => 'foo',
                    'contestName' => 'Test contest',
                ],
            ]))
            ->willReturn($expected)
            ;

        $actual = $this->mailer->notifyMusicians($request);

        $this->assertEquals($expected, $actual);
    }

    public function testNotifyMusiciansCallbackViewData()
    {
        $request = new Request();
        $request->attributes = [
            'contact_name' => 'foo',
            'contact_email' => 'foo@bar.com',
        ];
        $request->contest = new Contest();
        $request->contest->title = 'Test contest';

        $expected = true;
        $this->hmailer
            ->expects($this->once())
            ->method('send')
            ->with($this->equalTo([
                'to' => $request->contact_email,
                'viewData' => [
                    'fullName' => $request->contact_name,
                    'customData' => 'customData',
                    'contestName' => 'Test contest',
                ],
            ]))
            ->willReturn($expected)
            ;

        $actual = $this->mailer->notifyMusicians($request, [
            'viewData' => function (Request $request) {
                return [
                    'customData' => 'customData',
                ];
            },
        ]);

        $this->assertEquals($expected, $actual);
    }

    public function testNotifyAdmin()
    {
        $request = new Request();
        $request->attributes = [
            'contact_name' => 'foo',
            'contact_email' => 'foo@bar.com',
        ];
        $request->contest = new Contest();
        $request->contest->title = 'Test contest';

        $expected = true;
        $this->hmailer
            ->expects($this->once())
            ->method('send')
            ->with($this->equalTo([
                'to' => 'admin@foo.bar',
                'viewData' => [
                    'fullName' => $request->contact_name,
                    'contestName' => 'Test contest',
                    'customData' => 'customData',
                ],
            ]))
            ->willReturn($expected)
            ;

        $actual = $this->mailer->notifyAdmin($request, [
            'viewData' => [
                'customData' => 'customData',
            ],
        ]);

        $this->assertEquals($expected, $actual);
    }

    public function testRender()
    {
        $expected = 'passed';
        $this->hmailer
            ->expects($this->once())
            ->method('render')
            ->with($this->equalTo([
                'viewData' => [
                    'custom' => 'custom',
                ],
            ]))
            ->willReturn($expected)
            ;

        $actual = $this->mailer->render([
            'viewData' => [
                'custom' => 'custom',
            ],
        ]);

        $this->assertEquals($expected, $actual);
    }

    public function testSendConfirmations()
    {
        $contestId = new ContestId(1);
        $composition = new Composition();
        $composition->attributes = [
            'author' => 'author',
            'title' => 'title',
        ];

        $requestMock = $this->getMockBuilder(Request::class)
            ->setMethods(['save'])
            ->getMock();
        $requestMock->attributes = [
            'contact_name' => 'foo',
            'contact_email' => 'foo@bar.mail',
        ];
        $requestMock->primaryKey = 123;
        $requestMock->compositions = [
            $composition,
            $composition,
        ];
        $requestMock->contest = new Contest();
        $requestMock->contest->title = 'Test contest';

        $requests = [
            $requestMock,
            $requestMock,
        ];

        $this->repository
            ->expects($this->once())
            ->method('findNotConfirmed')
            ->with($this->identicalTo($contestId))
            ->willReturn($requests)
            ;

        // TODO: separate service for confirmation key and url generation
        // TODO: new ConfirmationEmail($request);
        $confirmationKey = $requestMock->getConfirmationKey();
        $this->hmailer
            ->expects($this->exactly(count($requests)))
            ->method('send')
            ->with($this->equalTo([
                'to' => $requestMock->contact_email,
                'from' => 'contest@estrocksection.kiev.ua',
                'subject' => 'Подтверждение участия в конкурсе «Рок єднає нас» 2016',
                'view' => 'request_confirm',
                'viewData' => [
                    'fullName' => $requestMock->contact_name,
                    'contestName' => 'Test contest',
                    'firstComposition' => $composition->getFullName(),
                    'secondComposition' => $composition->getFullName(),
                    'confirmationUrl' => \Yii::app()->createAbsoluteUrl('contest/contest/confirm', [
                        'id' => $requestMock->primaryKey,
                        'key' => $confirmationKey,
                    ]),
                ],
            ]))
            ->willReturn(true)
            ;

        $requestMock->expects($this->exactly(count($requests)))->method('save');

        $this->mailer->sendConfirmations($contestId);

        $this->assertEquals(
            Request::STATUS_WAIT_CONFIRM,
            $requestMock->status
        );
    }
}
