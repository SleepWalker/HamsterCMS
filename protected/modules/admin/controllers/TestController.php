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
            $html = \Yii::app()->request->getPost('html');
            $mpdf = new \mPDF();
            $mpdf->WriteHTML($html);
            $mpdf->Output();
            \Yii::app()->end();
        } else {
            $this->render('mpdf');
        }
    }
}
