<?php
/**
 * UpdateController class for admin module
 *
 * Производит обновление файлов цмс
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.admin.controllers.UpdateController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

namespace admin\controllers;

class UpdateController extends \admin\components\HAdminController
{
    public $defaultAction = 'db';

    /**
     * @vararray $dirMap хранит карту с директориями цмс и их хешами
     * array(
     *   'fileList' => array(
     *     'path1',
     *     'path2',
     *     'path/to/directory/too',
     *   ),
     *   'hashList' => array(
     *     'path/to/file/1.php' => hash,
     *     'path/to/file/2.php' => hash,
     *   ),
     * )
     */
    protected $_dirMap;

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
                'roles'=>array('admin'),
            ),
            array('deny',  // deny all users
            'users'=>array('*'),
            ),
        );
    }

    /**
     * @return меню для табов
     */
    public function tabs()
    {
        $updateList = $this->dbUpdateList; // модули к обновлению
        $updateCount = $updateList ? ' (<b style="text-decoration: blink;color:orange;">' . count($updateList) . '</b>)' : '';

        return array(
            'fs'  => 'Обновление ФС',
            'db'  => 'Обновление БД'.$updateCount,
            'download' => 'Загрузка модулей',
        );
    }

    public function actionFs()
    {
        $enModsIds = array_keys($this->enabledModules);
        $aliases = array(
            'application.components',
            'application.controllers',
            'application.extensions',
            'application.models',
            'application.vendor',
            'application.views',
            'application.widgets',
        );
        $enModsIds[] = 'admin';
        foreach ($enModsIds as $enModId) {
            $aliases[] = 'application.modules.' . $enModId;
        }

        $ans = $this->updatesHashList;

        $deleteList = array(); // файлы к удалению
        $updateList = array(); // файлы к обновлению
        $ignoreList = array(); // файлы, которыe будут игнорироваться

        // массив с файлами, которые будут игнорироваться (не должны автоматически обновлятся)
        $tmpIgnoreList = \Yii::getPathOfAlias('application.config') . '/updateIgnoreList.php';
        if (is_file($tmpIgnoreList)) {
            $tmpIgnoreList = require($tmpIgnoreList);
            foreach ($tmpIgnoreList as $alias => $files) {
                $pref = str_replace(\Yii::getPathOfAlias('application'), '', \Yii::getPathOfAlias('application.'.$alias));
                foreach ($files as $file) {
                    $ignoreList[] = $pref . '/' . $file;
                }
            }
        }
        unset($tmpIgnoreList);

        foreach ($aliases as $alias) {
            $arr = $this->hashDir($alias, $ignoreList);
            if (!is_array($ans[$alias])) {
                continue; // нету такого алиаса
            }
            $deleteList = array_merge(
                $deleteList,
                array_diff(
                    $arr['pathList'],
                    $ans[$alias]['pathList'],
                    $ignoreList
                )
            );
            $updateList = array_merge(
                $updateList,
                array_diff(
                    $ans[$alias]['hashList'],
                    $arr['hashList']
                )
            );
        }

        // удаляем из $updateList файлы, которые присутствуют в $ignoreList
        foreach ($ignoreList as $file) {
            unset($updateList[$file]);
        }

        ob_start();
?>
    К удалению:
    <?php echo implode("\n", $deleteList); ?>
    К обновлению:
    <?php echo implode("\n", array_keys($updateList)); ?>
    Игнорируются:
    <?php echo implode("\n", $ignoreList); ?>
<?php
        $logMessage = ob_get_clean();

        if (isset($_POST['update'])) {
            $status = true;
            $rootDir = \Yii::getPathOfAlias('application');

            // загружаем обновления
            if (count($updateList)) {
                $status = $status && $this->getUpdates(array_keys($updateList));
            }

            // удаляем старые файлы
            if (count($deleteList)) {
                foreach ($deleteList as $file) {
                    $fileToDelete = $rootDir . $file;
                    if (is_dir($fileToDelete)) {
                        $this->destroyDir($fileToDelete);
                    } elseif (file_exists($fileToDelete)) {
                        $status = $status && unlink($fileToDelete);
                    }
                }
            }

            // чистим assets и кэш
            $this->clearTmp();

            // сохраняем новую карту директорий
            \Yii::app()->cache->set('dirMap', $ans);

            // Пишем в лог
            Yii::log($logMessage, 'info', 'hamster.update');

            if ($status == true) {
                \Yii::app()->user->setFlash('success', 'Успешное обновление');
            } else {
                \Yii::app()->user->setFlash('fail', 'Во время обновления произошли ошибки');
            }

            $this->refresh();
        }

        $this->render('index', array(
            'deleteList' => $deleteList,
            'updateList' => $updateList,
            'ignoreList' => $ignoreList,
        ));
    }

    /**
     * Экшен отвечающий за обновление баз данных модулей
     *
     * @access public
     * @return void
     */
    public function actionDb()
    {
        $updateList = $this->getDbUpdateList(); // модули к обновлению

        ob_start();
?>
    К обновлению:
    <?= implode("\n", array_keys($updateList)); ?>
    <?php
        $logMessage = ob_get_clean();

        if (\Yii::app()->request->getPost('update')) {
            $status = true;
            // TODO: автобекап что бы можно было откатиться
            foreach ($updateList as $updateInfo) {
                $status = $status && $this->runDBUpdate($updateInfo);
            }

            // Пишем в лог
            \Yii::log($logMessage, 'info', 'hamster.update.db');

            if ($status === true) {
                \Yii::app()->user->setFlash('success', 'Успешное обновление');
            } else {
                \Yii::app()->user->setFlash('fail', 'Во время обновления произошли ошибки');
            }

            $this->refresh();
        }

        $viewUpdateList = [];
        foreach ($updateList as $moduleName => $updateInfo) {
            $viewUpdateList[] = $moduleName . " ({$updateInfo['moduleId']}: {$updateInfo['oldV']} -> {$updateInfo['newV']})";
        }

        $this->render('index', array(
            'updateList' => $viewUpdateList,
        ));
    }

    /**
     * Список модулей, которым необходимо обновление БД
     *
     * @access protected
     * @return array
     */
    protected function getDbUpdateList()
    {
        $updateList = array(); // модули к обновлению
        foreach (array_keys($this->enabledModules) as $moduleId) {
            $config = \admin\models\Config::load($moduleId); // конфиг, в котором лежит актуальная версия бд
            if (!$config) {
                continue;
            }

            $config = $config->adminConfig;
            $newV = (string)$config['db']['version'];
            if (!isset($this->modulesInfo[$moduleId]['db']['version'])) {
                \Yii::app()->user->setFlash('error', 'Ошибка в конфигурации модуля '.$moduleId.'. Отсутствует информация о базе данных');
            }

            $oldV = (string)$this->modulesInfo[$moduleId]['db']['version'];
            if ($newV != $oldV) {
                $updateList[$this->modulesInfo[$moduleId]['title']] = array(
                    'moduleId' => $moduleId,
                    'newV' => $newV,
                    'oldV' => $oldV,
                );
            }
        }

        return $updateList;
    }

    /**
     * Позволяет загружать модули, которых еще нету в цмс с сервера обновлений hamster
     *
     * @access public
     * @return void
     */
    public function actionDownload()
    {
        if (isset($_POST['moduleList'])) {
            $this->getModules($_POST['moduleList'], $_POST['password']);
            \Yii::app()->user->setFlash('success', 'Успешная загрузка новых модулей');

            $this->refresh();
        }
        ob_start();
        echo '<div class="form">' . \CHtml::beginForm() .
            '<div class="row" id="moduleList"></div><div class="row">' .
        \CHtml::passwordField('password', '', array('placeholder' => 'Введите пароль')) .
            '</div><div class="row">' .
        \CHtml::submitButton('Загрузить') .
            '</div>' .
            \CHtml::endForm() . '</div>';
?>
        <script>
        $.ajax('http://<?php if (YII_DEBUG) echo 'www.'; ?>update.hamstercms.com?action=getModuleList',{
                dataType: 'jsonp',
                    success: function(data) {
                        var $container = $('#moduleList');
                    for(var value in data)
                        $container.append($('<div class="row"></div>')
                        .append(
                            $('<input type="checkbox" name="moduleList[]">')
                            .prop('id', value)
                        .val(value)
                    )
                        .append($('<label for="' + value + '">' + data[value] + '</label>')));
                },
        });
        </script>
<?php
        $this->renderText(ob_get_clean());
    }

    /**
     * Запускает обновление базы данных для конкретного модуля
     *
     * @param array $updateInfo массив в котором находятся три элемента - moduleId, oldV, newV
     * @access protected
     * @return boolean true если обновление прошло успешно
     */
    protected function runDBUpdate($updateInfo)
    {
        return \HUpdateDb::instance($updateInfo['moduleId'])
                           ->runUpdates((string) $updateInfo['oldV'], (string) $updateInfo['newV']);
    }

    /**
     * Возвращает массив в котором находится структура директорий цмс в формате,
     * удобном для проверки актуальности файлов
     *
     * @param mixed $alias alias директории, для которой будет строиться массив
     * @param array $ignoreList массив с путями к файлам, которые надо игнорировать при обновлении
     * @access protected
     * @return array массив с путями и их хэшами
     */
    protected function hashDir($alias)
    {
        // возвращаем значение из карты (в случае если она уже закеширована)
        if (is_array($this->dirMap[$alias])) {
            return $this->dirMap[$alias];
        }

        $dir = \Yii::getPathOfAlias($alias);

        $pathList = $hashList = array();

        if (is_dir($dir)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            $root = \Yii::getPathOfAlias('application');
            foreach ($iterator as $file) {
                //if(substr($file->getBasename(), 0, 1) == '.' && $file->getBasename() != '.htaccess') continue; // пропускаем скрыте файлы (линукс)
                // пропускаем папки runtime, так как в них будет хранится инфа, которая зависит от конкретного сайта
                if ($file->getBasename() == 'runtime') {
                    continue;
                }
                $path = str_replace($root, '', (string)$file);

                // Игнорим .. и .
                if ($file->getBasename() == '.' || $file->getBasename() == '..') {
                    continue;
                }

                $pathList[] = $path;
                if ($file->isFile()) {
                    $hashList[$path] = md5_file((string)$file);
                }
            }
        }
        return array(
            'pathList' => $pathList, // сюда входят и директории тоже
            'hashList' => $hashList,
        );
    }

    /**
     * Инициализирует запрос к серверу обновлений и возвращает
     * массив со структурой директорий эталона.
     *
     * @access protected
     * @return array массив для сравнения с массивом полученным от {@link hashDir}
     */
    protected function getUpdatesHashList()
    {
        try {
            $ans = $this->requestFromUpdateServer([
                'action' => 'getFileList',
            ]);
        } catch (\Exception $e) {
            \Yii::app()->user->setFlash('error', 'Сервер обновлений не доступен');
            $this->refresh();
        }

        return unserialize($ans);
    }

    /**
     * Производит запрос на загрузку модулей к серверу обновлений
     *
     * @param array $moduleList
     * @access protected
     * @return boolean возвращает true, если в случае успешного обновления файлов CMS
     */
    protected function getModules(array $moduleList, $password)
    {
        $saveTo = \Yii::getPathOfAlias('application.runtime') . DIRECTORY_SEPARATOR . 'update.zip';

        try {
            $this->downloadPackage([
                'action' => 'getModules',
                'moduleList' => serialize($moduleList),
                'password' => $password,
            ], $saveTo);
        } catch (\Exception $e) {
            \Yii::app()->user->setFlash('error', 'Не правильный пароль или сервер обновлений недоступен');
            $this->refresh();
        }

        $this->doSourceUpdate($saveTo);
    }

    /**
     * Производит запрос файлов обновлений к серверу обновлений
     *
     * @param array $fileList массив с путями файлов, которые нужно обновить
     * @access protected
     * @return boolean возвращает true, если в случае успешного обновления файлов CMS
     */
    protected function getUpdates(array $fileList)
    {
        $saveTo = \Yii::getPathOfAlias('application.runtime') . DIRECTORY_SEPARATOR . 'update.zip';

        try {
            $this->downloadPackage([
                'action' => 'getUpdates',
                'fileList' => serialize($fileList),
            ], $saveTo);
        } catch (\Exception $e) {
            \Yii::app()->user->setFlash('error', 'Сервер обновлений недоступен');
            $this->refresh();
        }

        $this->doSourceUpdate($saveTo);
    }

    /**
     * @param  array  $data        post data for update server api
     * @param  string $destination where to save downloaded file
     * @throws Exception IF can't get data from update server
     * @return void
     */
    private function downloadPackage(array $data, $destination)
    {
        $data = $this->requestFromUpdateServer($data);

        file_put_contents($to, $data);
    }


    /**
     * @param  array  $data        post data for update server api
     * @throws Exception IF can't get data from update server
     * @return string $data
     */
    private function requestFromUpdateServer(array $data)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => 'http://www.update.hamstercms.com/',
            CURLOPT_USERAGENT => 'Hamster Updater',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
        ]);

        $data = curl_exec($curl);
        curl_close($curl);

        if (!$data) {
            throw new \Exception("Can't download data from update server");
        }

        return $data;
    }

    /**
     * @param  string $source path to zip archive with source update
     * @throws Exception IF can't extract archive
     * @return void
     */
    private function doSourceUpdate($source)
    {
        $destination = \Yii::getPathOfAlias('application');

        try {
            $this->extractZip($source, $destination);
            unlink($source);
        } catch (\Exception $e) {
            throw new \Exception("Can't extract zip file: $source", 0, $e);
        }
    }

    /**
     * @throws Exception IF can't extract archive
     * @return void
     */
    private function extractZip($source, $destination)
    {
        $zip = new \ZipArchive;
        if ($zip->open($source) === true) {
            if ($zip->extractTo($destination) === false) {
                throw new \Exception("Can't extract an archive");
            }

            $zip->close();
        } else {
            throw new \Exception("Can't open zip file: $source");
        }
    }

    /**
     * Возвращает закэшированный массив с картой директорий полученных
     * от {@link hashDir}, если он есть. Иначе вернется пустой массив
     *
     * @access public
     * @return array массив с картой директорий полученных от {@link hashDir}
     */
    public function getDirMap()
    {
        if (!isset($this->_dirMap)) {
            $this->_dirMap = \Yii::app()->cache->get('dirMap');
        }
        return $this->_dirMap;
    }
}
