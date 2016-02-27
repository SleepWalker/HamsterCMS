<?php
/**
 * Mailer wrapper for sending emails from hamstercms
 * This wrapper is similar to yii-mail, but with slightly simplier better api
 * In the same time it's supports fallback to YiiMail::send() method of yii-mailer (but ignores second, optional arg)
 */

namespace ext\hamster;

\Yii::import('ext.yii-mail.YiiMailMessage');
\Yii::import('ext.yii-mail.YiiMail');

class Mailer extends \CApplicationComponent
{
    /**
     * @var array settings for yii-mail.YiiMail compoenent
     */
    public $mailerConfig = [];

    private $_mailer;

    public function send($params)
    {
        if ($params instanceof \YiiMailMessage) {
            return $this->getMailer()->send($params);
        }

        if (!isset($params['subject'])) {
            throw new \Exception('The subject is required');
        }

        $params = $this->normalizeParams($params);

        $m = $this->composeEmail($params);

        return $this->getMailer()->send($m);
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
        return \CMap::mergeArray(array(
            'from' => \Yii::app()->params['noReplyEmail'],
            'viewData' => array(),
            'attachments' => array(),
        ), $params);
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

    private function addTo($m, $params)
    {
        $to = is_array($params['to']) ? $params['to'] : [$params['to']];

        foreach ($to as $email) {
            $m->addTo($email);
        }
    }

    private function addAttachments($m, $params)
    {
        foreach ($params['attachments'] as $file) {
            $swiftAttachment = \Swift_Attachment::fromPath($file);
            $m->attach($swiftAttachment);
        }
    }

    private function setMessage($m, $params)
    {
        $m->setBody($this->render($params), 'text/html');
    }

    private function renderMessage($view, $data)
    {
        $oldRenderer = \Yii::app()->getViewRenderer();
        $mdRenderer = \Yii::createComponent(array(
            'class' => '\ext\hamster\MdViewRenderer',
        ));
        \Yii::app()->setComponent('viewRenderer', $mdRenderer);

        $output = \Yii::app()->controller->renderPartial($view, $data, true);

        \Yii::app()->setComponent('viewRenderer', $oldRenderer);

        return $output;
    }

    private function getMailer()
    {
        if (!isset($this->_mailer)) {
            $this->_mailer = \Yii::createComponent(\CMap::mergeArray([
                'class' => \YiiMail::class,
            ], $this->mailerConfig));
        }

        return $this->_mailer;
    }
}
