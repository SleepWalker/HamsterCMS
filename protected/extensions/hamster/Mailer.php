<?php
/**
 * Mailer wrapper for sending emails from hamstercms
 * This wrapper is similar to yii-mail, but with slightly simplier better api
 * In the same time it's supports fallback to YiiMail::send() method of yii-mailer (but ignores second, optional arg)
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    contest.controllers
 * @copyright  Copyright &copy; 2015 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

namespace ext\hamster;

\Yii::import('ext.yii-mail.YiiMailMessage');
\Yii::import('ext.yii-mail.YiiMail');

class Mailer extends \CApplicationComponent
{
    /**
     * @var array settings for yii-mail.YiiMail compoenent
     */
    public $mailerConfig = array();

    protected $_mailer;

    public function send($params)
    {
        if ($params instanceof \YiiMailMessage) {
            return $this->mailer->send($params);
        }

        $params = $this->normalizeParams($params);

        $m = $this->composeEmail($params);

        return $this->getMailer()->send($m);
    }

    protected function normalizeParams($params)
    {
        return \CMap::mergeArray(array(
            'from' => \Yii::app()->params['noReplyEmail'],
            'viewData' => array(),
            'attachments' => array(),
        ), $params);
    }

    protected function composeEmail($params)
    {
        $m = new \YiiMailMessage();
        $m->addTo($params['to']);
        $m->from = $params['from'];
        $m->subject = strip_tags($params['subject']);
        $this->addAttachments($m, $params);
        $this->setMessage($m, $params);

        return $m;
    }

    protected function addAttachments($m, $params)
    {
        foreach ($params['attachments'] as $file) {
            $swiftAttachment = \Swift_Attachment::fromPath($file);
            $m->attach($swiftAttachment);
        }
    }

    protected function setMessage($m, $params)
    {
        if (isset($params['view'])) {
            $message = $this->renderMessage($params['view'], $params['viewData']);
        } elseif (isset($params['message'])) {
            $message = $params['message'];
        } else {
            $message = '';
        }

        $m->setBody($message, 'text/html');
    }

    protected function renderMessage($view, $data)
    {
        $mdRenderer = \Yii::createComponent(array(
            'class' => '\ext\hamster\MdViewRenderer',
        ));
        \Yii::app()->setComponent('viewRenderer', $mdRenderer);

        $output = \Yii::app()->controller->renderPartial($view, $data, true);

        \Yii::app()->setComponent('viewRenderer', null);

        return $output;
    }

    protected function getMailer()
    {
        if (!isset($this->_mailer)) {
            $this->_mailer = \Yii::createComponent(\CMap::mergeArray(array(
                'class' => 'YiiMail',
            ), $this->mailerConfig));
        }

        return $this->_mailer;
    }
}
