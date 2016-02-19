<?php
/**
 * AdminController class for admin module
 */

namespace admin\controllers;

\Yii::import('admin.extensions.yii-database-dumper.SDatabaseDumper');

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
        $filePath = \Yii::getPathOfAlias('application.runtime.backup') . DIRECTORY_SEPARATOR;

        // Скачивание бекапа
        if (isset($_GET['download']) && !empty($_GET['download'])) {
            $this->actionDownload($_GET['download']);
        }

        // Восстановление из бекапа
        if (isset($_GET['restore']) && $_GET['restore']) {
            $this->actionRestore($_GET['restore']);
        }

        // удаление бекапа
        if (isset($_GET['delete']) && $_GET['delete']) {
            $this->actionDelete($_GET['delete']);
        }

        if (\Yii::app()->request->isPostRequest) {
            if (isset($_POST['flushDb']) && $_POST['flushDb']) {
                $this->actionFlush();
            } elseif (isset($_FILES['dump'])) {
                $this->actionUpload($_FILES['dump']);
            } else {
                $this->actionBackupAll();
            }
        }

        // список файлов в директории
        $fileListOfDirectory = array();
        $pathTofileListDirectory = \Yii::getPathOfAlias('application.runtime.backup');
        foreach (new \DirectoryIterator($pathTofileListDirectory) as $file) {
            if ($file->isFile() === true) {
                array_push($fileListOfDirectory, [
                    'name' => $file->getBasename(),
                    'size' => $file->getSize(),
                    'formattedSize' => $this->formatSize($file->getSize()),
                    'time' => $file->getMTime(),
                ]);
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

    private function actionBackupAll()
    {
        $file = $this->getBackupDir() . '/' . $this->generateDumpName();

        $this->backupToFile($file);

        $this->refresh();
    }

    private function backupToFile($destination, $options = array())
    {
        $dumper = $this->getDumper();
        $dump = $dumper->getDump($options);

        if (function_exists('gzencode')) {
            file_put_contents($destination . '.gz', gzencode($dump));
        } else {
            file_put_contents($destination, $dump);
        }
    }

    private function generateDumpName($prefix = '')
    {
        if (!empty($prefix)) {
            $prefix .= '_';
        }

        return $prefix . 'dump_hamster_' . substr(\Yii::app()->request->getHostInfo(null), 3) . '_' . date('Y-m-d_H_i_s') . '.sql';
    }

    private function actionDownload($file)
    {
        $sqlFile = $this->getSqlFile($file);

        header('Content-Disposition: attachment; filename="' . basename($sqlFile) . '"');
        header('Content-type: application/x-gzip; name=' . basename($sqlFile));
        header('Content-Length: ' . filesize($sqlFile));

        readfile($sqlFile);
        \Yii::app()->end();
    }

    private function actionDelete($file)
    {
        $sqlFile = $this->getSqlFile($file);
        if (unlink($sqlFile) === true) {
            \Yii::app()->user->setFlash('success', 'Бекап ' . $file . ' успешно удален');
        } else {
            \Yii::app()->user->setFlash('error', 'Не удалось удалить бекап ' . $file);
        }

        $this->redirect(['/admin/backup']);
    }

    private function actionRestore($file)
    {
        $sqlFile = $this->getSqlFile($file);
        $sql = file_get_contents($sqlFile);
        if (strpos($sqlFile, 'gz')) {
            $sql = gzinflate(substr($sql, 10, -8));
        }

        $rowCount = \Yii::app()->db->createCommand($sql)->execute();

        \Yii::app()->user->setFlash('success', 'База успешно восстановлена. Затронуто строк: ' . $rowCount);

        // TODO: сделать отправку на восстановление из бекапа через пост
        $this->redirect(['/admin/backup']);
    }

    private function actionFlush()
    {
        $dumper = $this->getDumper();
        if ($dumper->flushDb()) {
            \Yii::app()->user->setFlash('success', 'База успешно очищена');
        } else {
            \Yii::app()->user->setFlash('error', 'При очистке базы данных произошла ошибка');
        }

        $this->refresh();
    }

    private function actionUpload($file)
    {
        $isSql = strpos($file['type'], 'sql') !== false;
        $isZip = strpos($file['type'], 'gz') !== false;

        if (!$isSql && !$isZip) {
            \Yii::app()->controller->refresh();
        }

        $destination = $this->getBackupDir() . '/' . $file['name'];
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            @chmod($destination, 0777);
            \Yii::app()->user->setFlash('success', 'Файл ' . $file['name'] . ' успешно загружен');
        } else {
            \Yii::app()->user->setFlash('error', 'Не удалось загрузить файл ' . $file['name']);
        }

        $this->refresh();
    }

    /**
     * Производит базовую фильтрацию имени файла
     *
     * @param  string $fileName имя файла
     *
     * @return string отфильтрованное имя файла с путем к нему
     */
    private function getSqlFile($fileName)
    {
        $fileName = str_replace('/', '', $fileName);

        $sqlFile = $this->getBackupDir() . DIRECTORY_SEPARATOR . $fileName;

        if (!is_readable($sqlFile)) {
            throw new \CException('Backup file can not be read');
        }

        return $sqlFile;
    }

    /**
     * @return string путь к директории с бекапами
     */
    private function getBackupDir()
    {
        $backupDir = \Yii::app()->runtimePath . DIRECTORY_SEPARATOR . 'backup';
        if (!is_dir($backupDir)) {
            mkdir($backupDir);
        }

        return $backupDir;
    }

    private function formatSize($value, $decimals = 2, $base = 1024)
    {
        $units = ['B','KB','MB','GB','TB'];
        for ($i = 0; $base <= $value; $i++) {
            $value = $value / $base;
        }

        return round($value, $decimals) . $units[$i];
    }

    /**
     * @return SDatabaseDumper
     */
    protected function getDumper()
    {
        return new \SDatabaseDumper(\Yii::app()->db);
    }
}
