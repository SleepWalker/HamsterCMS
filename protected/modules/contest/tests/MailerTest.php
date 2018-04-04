<?php
use contest\components\Mailer;
use contest\models\Request;
use contest\models\Contest;
use contest\models\Composition;
use ext\hamster\Mailer as HamsterMailer;

class MailerTest extends \CTestCase
{
    private $mailer;
    private $hmailer;
    private $repository;

    public function setUp()
    {
        $this->hmailer = $this->getMockBuilder(HamsterMailer::class)->getMock();

        $this->mailer = new Mailer($this->hmailer, 'admin@foo.bar');
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
        $composition = new Composition();
        $composition->attributes = [
            'author' => 'author',
            'title' => 'title',
        ];

        $request = new Request();
        $request->attributes = [
            'contact_name' => 'foo',
            'contact_email' => 'foo@bar.mail',
        ];
        $request->primaryKey = 123;
        $request->compositions = [
            $composition,
            $composition,
        ];
        $request->contest = new Contest();
        $request->contest->title = 'Test contest';

        $confirmationKey = $request->getConfirmationKey();
        $this->hmailer
            ->expects($this->once())
            ->method('send')
            ->with($this->equalTo([
                'to' => $request->contact_email,
                'from' => 'contest@estrocksection.kiev.ua',
                'subject' => 'Подтверждение участия в конкурсе «Рок єднає нас» 2016',
                'view' => 'request_confirm',
                'viewData' => [
                    'fullName' => $request->contact_name,
                    'contestName' => 'Test contest',
                    'firstComposition' => $composition->getFullName(),
                    'secondComposition' => $composition->getFullName(),
                    'confirmationUrl' => \Yii::app()->createAbsoluteUrl('contest/contest/confirm', [
                        'id' => $request->primaryKey,
                        'key' => $confirmationKey,
                    ]),
                ],
            ]))
            ->willReturn(true)
            ;

        $this->mailer->sendConfirmation($request);
    }
}
