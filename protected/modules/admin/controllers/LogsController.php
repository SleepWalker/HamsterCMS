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
    private $filtersForm;

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
        // Create filter model and set properties
        // http://www.yiiframework.com/wiki/232/using-filters-with-cgridview-and-carraydataprovider/
        $filtersForm = new \FiltersForm;
        if (isset($_GET['FiltersForm'])) {
            unset($_GET['FiltersForm'][0]);
            $filtersForm->filters = $_GET['FiltersForm'];
        }
        $this->filtersForm = $filtersForm;

        $runtimePath = \Yii::app()->runtimePath;
        $availableLogs = $this->getRuntimeLogs($runtimePath);

        $selectedLogFile = isset($_GET['log']) && in_array($_GET['log'], $availableLogs) ? $_GET['log'] : reset($availableLogs);
        $logFilePath = $runtimePath . '/' . $selectedLogFile;

        list($logData, $categories) = $this->parseLog($logFilePath);

        $dataProvider = new \CArrayDataProvider($logData, [
            'id' => 'log',
            'keyField' => false,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->render('log', [
            'dataProvider' => $dataProvider,
            'filtersForm' => $filtersForm,
            'categories' => $categories,
            'availableLogs' => array_combine($availableLogs, $availableLogs),
        ]);
    }

    /**
     * Возвращает массив с данными лога для использования в CGridView
     *
     * @param string $logFilePath путь к файлу лога для парсинга
     *
     * @return $logData, $logCategories
     */
    private function parseLog($logFilePath)
    {
        if (!is_readable($logFilePath)) {
            throw new \InvalidArgumentException("$logFilePath is not readable or does not exists");
        }

        $filtersForm = $this->filtersForm;

        $logString = file_get_contents($logFilePath);

        // добавляем разделитель, по которому будем делить строку
        $logString = preg_replace('/^(\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2})/m', '--Separator--$0', $logString);

        // Добавляем еще один сепаратор, что бы отображалась и последняя запись в логе
        $logString .= '--Separator--';
        preg_match_all('/(\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2}) \[([^\]]+)\] \[([^\]]+)\] (.*?)--Separator--/s', $logString, $matches, PREG_SET_ORDER);
        $matches = array_reverse($matches);
        $logData = $filtersForm->filter($matches);

        $logCategories = [];
        foreach ($matches as $row) {
            $logCategories[$row[3]] = $row[3];
        }

        asort($logCategories);

        return [
            $logData,
            $logCategories,
        ];
    }

    /**
     * @param string $path путь к директории рантайма
     *
     * @return array Названия файлов логов в папке райнтайма $path
     */
    private function getRuntimeLogs($runtimePath)
    {
        $logPathes = glob($runtimePath . DIRECTORY_SEPARATOR . '*.log*');
        $logFiles = [];
        foreach ($logPathes as $logPath) {
            array_push($logFiles, basename($logPath));
        }

        return $logFiles;
    }
}
