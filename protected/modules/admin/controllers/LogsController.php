<?php
/**
 * AdminController class for admin module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.admin.controllers
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

namespace admin\controllers;

class LogsController extends \admin\components\HAdminController
{
    public function filters()
    {
        return array(
            'accessControl',
        );
    }

    public function accessRules()
    {
        return array(
            array('allow',
                'roles' => array('admin'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    public function actionIndex()
    {
        // Create filter model and set properties
        // http://www.yiiframework.com/wiki/232/using-filters-with-cgridview-and-carraydataprovider/
        $filtersForm = new \FiltersForm;
        if (isset($_GET['FiltersForm'])) {
            unset($_GET['FiltersForm'][0]);
            $filtersForm->filters = $_GET['FiltersForm'];
        }

        $logString = file_get_contents(\Yii::getPathOfAlias('application.runtime.application') . '.log');
        // добавляем разделитель, по которому будем делить строку
        $logString = preg_replace('/^(\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2})/m', '--Separator--$0', $logString);
        // Добавляем еще один сепаратор, что бы отображалась и последняя запись в логе
        $logString .= '--Separator--';
        preg_match_all('/(\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2}) \[([^\]]+)\] \[([^\]]+)\] (.*?)--Separator--/s', $logString, $matches, PREG_SET_ORDER);
        $matches = array_reverse($matches);
        $filteredData = $filtersForm->filter($matches);
        foreach ($matches as $row) {
            $categories[$row[3]] = $row[3];
        }

        asort($categories);

        $dataProvider = new \CArrayDataProvider($filteredData, array(
            'id' => 'log',
            'keyField' => false,
            'pagination' => array(
                'pageSize' => 20,
            ),
        ));

        $this->render('log', array(
            'dataProvider' => $dataProvider,
            'filtersForm' => $filtersForm,
            'categories' => $categories,
        ));
    }
}
