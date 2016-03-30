<?php

\Yii::import('contest.admin.ContestAdminController');

class RequestConfirmTest extends \CTestCase
{
    public function testEmailVariablesRendering()
    {
        \Yii::app()->setTheme(null);
        \Yii::app()->setController(
            new ContestAdminController('contest', \Yii::app()->getModule('contest'))
        );

        $data = [
            'fullName' => 'fullName',
            'contestName' => 'contestName',
            'firstComposition' => 'firstComposition',
            'secondComposition' => 'secondComposition',
            'confirmationUrl' => 'confirmationUrl',
        ];
        $text = \Yii::app()->getModule('contest')->mailer->render([
            'view' => 'request_confirm',
            'viewData' => $data,
        ]);

        $this->assertNotContains('{{', $text);
        $this->assertNotContains('}}', $text);

        foreach ($data as $value) {
            $this->assertContains($value, $text);
        }
    }
}
