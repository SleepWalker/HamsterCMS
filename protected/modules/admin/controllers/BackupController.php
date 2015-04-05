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

class BackupController extends \admin\components\HAdminController
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
        \Yii::import('admin.extensions.yii-database-dumper.SDatabaseDumper');

        if (!is_dir(\Yii::getPathOfAlias('application.runtime.backup'))) {
            mkdir(\Yii::getPathOfAlias('application.runtime.backup'));
        }
        // создаем директорию для дампов

        $filePath = \Yii::getPathOfAlias('application.runtime.backup') . DIRECTORY_SEPARATOR;

        // Восстановление из бекапа
        if (isset($_GET['restore']) && $_GET['restore']) {
            $sqlFile = $filePath . $_GET['restore'];
            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                if (strpos($sqlFile, 'gz')) {
                    $sql = gzinflate(substr($sql, 10, -8));
                }
                // чистим бд
                $dumper = new \SDatabaseDumper;
                $dumper->flushDb();

                // запускаем sql комманды
                $db = \Yii::app()->db;
                $command = $db->createCommand($sql);
                $rowCount = $command->execute();

                \Yii::app()->user->setFlash('success', 'База успешно восстановлена. Затронуто строк: ' . $rowCount);
            }
            // T!: сделать отправку на восстановление из бекапа через пост
            $this->redirect(array('/admin/backup'));
        }

        // удаление бекапа
        if (isset($_GET['delete']) && $_GET['delete']) {
            if (file_exists($filePath . $_GET['delete'])) {
                if (unlink($filePath . $_GET['delete']) === true) {
                    \Yii::app()->user->setFlash('success', 'Бекап ' . $_GET['delete'] . ' успешно удален');
                }

            }
            $this->redirect(array('/admin/backup'));
        }

        if (\Yii::app()->request->isPostRequest) {
            if (isset($_POST['flushDb']) && $_POST['flushDb']) {
                $dumper = new \SDatabaseDumper;
                if ($dumper->flushDb()) {
                    \Yii::app()->user->setFlash('success', 'База успешно очищена');
                }

            } else {
                $dumper = new \SDatabaseDumper;
                // Get path to backup file
                $file = $filePath . 'dump_' . date('Y-m-d_H_i_s') . '.sql';

                $dump = $dumper->getDump();
                // Gzip dump
                if (function_exists('gzencode')) {
                    file_put_contents($file . '.gz', gzencode($dump));
                } else {
                    file_put_contents($file, $dump);
                }

            }
            $this->refresh();
        }

        // список файлов в директории
        $fileListOfDirectory = array();
        $pathTofileListDirectory = \Yii::getPathOfAlias('application.runtime.backup');
        foreach (new \DirectoryIterator($pathTofileListDirectory) as $file) {
            if ($file->isFile() === true) {
                array_push($fileListOfDirectory, array(
                    'name' => $file->getBasename(),
                    'size' => $file->getSize(),
                    'time' => $file->getMTime(),
                ));
            }
        }

        $dataProvider = new \CArrayDataProvider($fileListOfDirectory, array(
            'keyField' => false,
            'pagination' => array(
                'pageSize' => 20,
            ),
            'sort' => array(
                'attributes' => array(
                    'time',
                ),
                'defaultOrder' => array(
                    'time' => \CSort::SORT_DESC,
                ),
            ),
        ));

        $this->render('backup', array(
            'dataProvider' => $dataProvider,
        ));
    }
}
