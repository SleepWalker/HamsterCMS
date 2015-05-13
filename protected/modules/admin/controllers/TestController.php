<?php
/**
 * A controller for testing purposes
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

namespace admin\controllers;

class TestController extends \admin\components\HAdminController
{
    public function tabs()
    {
        return [
            'mpdf' => 'mpdf',
            'md' => 'md',
        ];
    }
    public function filters()
    {
        return [
            'accessControl',
        ];
    }

    public function accessRules()
    {
        return [
            ['allow',
                'roles' => ['admin'],
            ],
            ['deny', // deny all users
                'users' => ['*'],
            ],
        ];
    }

    public function actionIndex()
    {
        $this->render('index');
    }

    public function actionMpdf()
    {
        if (\Yii::app()->request->isPostRequest) {
            $code = \Yii::app()->request->getPost('code');
            $mpdf = new \mPDF();
            $mpdf->WriteHTML($code);
            $mpdf->Output();
            \Yii::app()->end();
        } else {
            $this->render('code');
        }
    }

    public function actionMd()
    {
        if (\Yii::app()->request->isPostRequest) {
            $code = \Yii::app()->request->getPost('code');
            echo (new \CMarkdownParser())->transform($code);
            \Yii::app()->end();
        } else {
            $this->render('code');
        }
    }
}
