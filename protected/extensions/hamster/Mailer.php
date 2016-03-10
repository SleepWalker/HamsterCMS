<?php
/**
 * Mailer wrapper for sending emails from hamstercms
 * This wrapper is similar to yii-mail, but with slightly simplier better api
 * In the same time it's supports fallback to YiiMail::send() method of yii-mailer (but ignores second, optional arg)
 */

namespace ext\hamster;

use KoKoKo\assert\Assert;

\Yii::import('ext.yii-mail.YiiMailMessage');
\Yii::import('ext.yii-mail.YiiMail');

class Mailer extends \CApplicationComponent
{
    /**
     * @var array settings for yii-mail.YiiMail compoenent
     */
    public $mailerConfig = [];

    private $mailer;
    private $fromEmail;

    public function __construct(\YiiMail $mailer = null, $fromEmail = null)
    {
        $this->mailer = $mailer;

        if (!$fromEmail) {
            $fromEmail = \Yii::app()->params['noReplyEmail'];
        }
        $this->fromEmail = $fromEmail;
    }

    public function init()
    {
        parent::init();

        if (!$this->mailer) {
            $this->mailer = \Yii::createComponent(\CMap::mergeArray([
                'class' => \YiiMail::class,
            ], $this->mailerConfig));
        }
    }

    public function send($params)
    {
        if ($params instanceof \YiiMailMessage) {
            return $this->mailer->send($params);
        }

        Assert::assert($params, 'params')->isArray();

        if (!isset($params['subject'])) {
            throw new \InvalidArgumentException('The subject is required');
        }

        if (!isset($params['to'])) {
            throw new \InvalidArgumentException('The to email is required');
        }

        $params = $this->normalizeParams($params);

        $m = $this->composeEmail($params);

        return $this->mailer->send($m);
    }

    /**
     * Renders message as it will be send
     *
     * @param  array  $params
     *
     * @return string
     */
    public function render(array $params)
    {
        if (isset($params['view'])) {
            if (!isset($params['viewData'])) {
                $params['viewData'] = [];
            }
            $message = $this->renderMessage($params['view'], $params['viewData']);
        } elseif (isset($params['message'])) {
            $message = $params['message'];
        } else {
            $message = '';
        }

        return $message;
    }

    private function normalizeParams($params)
    {
        return \CMap::mergeArray([
            'from' => $this->fromEmail,
            'viewData' => [],
            'attachments' => [],
        ], $params);
    }

    private function composeEmail($params)
    {
        $m = new \YiiMailMessage();
        $m->from = $params['from'];
        $m->subject = strip_tags($params['subject']);
        $this->addTo($m, $params);
        $this->addAttachments($m, $params);
        $this->setMessage($m, $params);

        return $m;
    }

    private function addTo(\YiiMailMessage $m, array $params)
    {
        $to = is_array($params['to']) ? $params['to'] : [$params['to']];

        foreach ($to as $email) {
            $m->addTo($email);
        }
    }

    private function addAttachments(\YiiMailMessage $m, array $params)
    {
        foreach ($params['attachments'] as $file) {
            $swiftAttachment = \Swift_Attachment::fromPath($file);
            $m->attach($swiftAttachment);
        }
    }

    private function setMessage(\YiiMailMessage $m, array $params)
    {
        $m->setBody($this->render($params), 'text/html');
    }

    private function renderMessage($view, array $data)
    {
        Assert::assert($view, 'view')->string();

        $oldRenderer = \Yii::app()->getViewRenderer();
        $mdRenderer = \Yii::createComponent([
            'class' => '\ext\hamster\MdViewRenderer',
        ]);
        \Yii::app()->setComponent('viewRenderer', $mdRenderer);

        $output = \Yii::app()->controller->renderPartial($view, $data, true);

        \Yii::app()->setComponent('viewRenderer', $oldRenderer);

        return $output;
    }
}
