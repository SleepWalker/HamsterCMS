<?php
use contest\components\Mailer;
use contest\models\Request;

class MailerTest extends \CTestCase
{
    private $mailer;
    private $hmailer;

    public function setUp()
    {
        $this->hmailer = $this->getMockBuilder('ext\hamster\Mailer')->getMock();

        $this->mailer = new Mailer($this->hmailer, 'admin@foo.bar');
    }

    public function testSuccessNotifyMusicians()
    {
        $request = new Request();
        $request->attributes = [
            'contact_name' => 'foo',
            'contact_email' => 'foo@bar.com',
        ];

        $expected = true;

        $this->hmailer
            ->expects($this->once())
            ->method('send')
            ->with($this->equalTo([
                'to' => $request->contact_email,
                'viewData' => [
                    'fullName' => $request->contact_name,
                ],
            ]))
            ->willReturn($expected)
            ;

        $actual = $this->mailer->notifyMusicians($request, []);

        $this->assertEquals($expected, $actual);
    }

    public function testNotifyMusiciansCallbackViewData()
    {
        $request = new Request();
        $request->attributes = [
            'contact_name' => 'foo',
            'contact_email' => 'foo@bar.com',
        ];

        $expected = true;
        $this->hmailer
            ->expects($this->once())
            ->method('send')
            ->with($this->equalTo([
                'to' => $request->contact_email,
                'viewData' => [
                    'fullName' => $request->contact_name,
                    'customData' => 'customData',
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

        $expected = true;
        $this->hmailer
            ->expects($this->once())
            ->method('send')
            ->with($this->equalTo([
                'to' => 'admin@foo.bar',
                'viewData' => [
                    'fullName' => $request->contact_name,
                ],
            ]))
            ->willReturn($expected)
            ;

        $actual = $this->mailer->notifyAdmin([
            'to' => $request->contact_email,
            'viewData' => [
                'fullName' => $request->contact_name,
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
}
