<?php

\Yii::import('ext.yii-mail.YiiMailMessage');
\Yii::import('ext.yii-mail.YiiMail');

use ext\hamster\Mailer;
use contest\models\Request;
use \YiiMailMessage as YiiMailMessage;

class MailerTest extends \CTestCase
{
    private $mailer;
    private $hmailer;
    private $fromEmail = 'no-reply@bar.com';

    public function setUp()
    {
        $this->yiiMail = $this->getMockBuilder('\YiiMail')->getMock();

        $this->mailer = new Mailer($this->yiiMail, $this->fromEmail);
    }

    public function testShouldInstantlySendIfYiiMailMessage()
    {
        $message = new YiiMailMessage();

        $expected = 'foo';
        $this->yiiMail
            ->expects($this->once())
            ->method('send')
            ->with($this->identicalTo($message))
            ->willReturn($expected)
            ;

        $actual = $this->mailer->send($message);

        $this->assertEquals($expected, $actual);
    }

    public function testSubjectRequired()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The subject is required');

        $this->mailer->send([]);
    }

    public function testToRequired()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The to email is required');

        $this->mailer->send([
            'subject' => 'test',
        ]);
    }

    public function testComposeEmail()
    {
        $params = [
            'subject' => 'test',
            'to' => 'foo@bar.com',
            'message' => 'test',
            'attachments' => [
                'foo/bar.jpg',
            ],
        ];
        $expected = 'foo';
        $this->yiiMail
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (YiiMailMessage $message) use ($params) {
                $this->assertEquals($params['subject'], $message->getSubject());
                $this->assertEquals($params['message'], $message->getBody());
                $this->assertEquals(basename($params['attachments'][0]), $message->getChildren()[0]->getFilename());
                $this->assertEquals([$params['to'] => null], $message->getTo());
                $this->assertEquals([$this->fromEmail => null], $message->getFrom());

                return true;
            }))
            ->willReturn($expected)
            ;

        $actual = $this->mailer->send($params);

        $this->assertEquals($expected, $actual);
    }

    public function testRenderEmpty()
    {
        $expected = '';
        $actual = $this->mailer->render([]);

        $this->assertEquals($expected, $actual);
    }

    public function testRenderPlainMessage()
    {
        $expected = 'foo bar';
        $actual = $this->mailer->render([
            'message' => $expected,
        ]);

        $this->assertEquals($expected, $actual);
    }

    public function testRenderView()
    {
        $params = [
            'view' => 'test',
            'viewData' => [
                'foo' => 'bar',
            ],
        ];
        $expected = 'foo bar';

        $controller = $this->getMockBuilder('\Controller')
            ->setConstructorArgs(['foo'])
            ->getMock()
            ;
        $controller
            ->expects($this->once())
            ->method('renderPartial')
            ->with(
                $this->identicalTo($params['view']),
                $this->identicalTo($params['viewData'])
            )
            ->willReturn($expected)
            ;

        \Yii::app()->setController($controller);

        $actual = $this->mailer->render($params);

        $this->assertEquals($expected, $actual);
    }
}
