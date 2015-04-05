<?php
/**
 * HUpdateDb базовый класс для обновления БД модулей hamster
 *
 * @abstract
 * @package hamster.modules.admin.components
 * @version $id$
 * @copyright Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @author Sviatoslav Danylenko <mybox@udf.su>
 * @license PGPLv3 ({@link http://www.gnu.org/licenses/gpl-3.0.html})
 */
abstract class HUpdateDb extends CDbMigration
{
    /**
     * @property string $moduleId идентификатор модуля, для которого создан экземпляр класса
     */
    protected $moduleId;

    /**
     * @property array $logStack стэк лога для процесса обновления
     */
    protected $logStack = array();

    /**
     * @property boolean $rawSqlStarted флаг, говорящий о том, что метод {@link HUpdateDB::startRawSql()} был вызван и вместе с ним соответственно ob_start()
     */
    protected $rawSqlStarted = false;

    /**
     * Инициализирует свойства класса
     *
     * @param mixed $moduleId
     * @final
     * @access protected
     * @return void
     */
    final protected function __construct($moduleId)
    {
        // проверяем можем ли мы писать в необходимых директориях
        $path = Yii::getPathOfAlias('application.config') . '/hamster.php';
        if (!is_writable($path)) {
            Yii::app()->user->setFlash('error', "Файл '$path' не доступен для записи.");
        }
        $path = Yii::getPathOfAlias('application.config') . '/hamsterModules.php';
        if (!is_writable($path)) {
            Yii::app()->user->setFlash('error', "Файл '$path' не доступен для записи.");
        }
        $this->moduleId = $moduleId;
        $this->setDbConnection(Yii::app()->db);

        // подключаем модели модуля
        Yii::app()->controller->module->setImport(array(
            $moduleId . '.models.*',
        ));

        $this->init();
    }

    public static function instance($moduleId)
    {
        Yii::import('application.modules.' . $moduleId . '.admin.UpdateDb', true);

        $updater = new UpdateDb($moduleId);
        return $updater;
    }

    /**
     * Метод, который вызывается сразу после инициализации
     * может быть переопределен, для добавления необходимых операций на этапе инициализации
     *
     * @access protected
     * @return void
     */
    protected function init()
    {
    }

    /**
     * Метод должен возвращать массив с историей версий БД вида: array(1,1.1,1.2.3,2...)
     *
     * @abstract
     * @access public
     * @return array история БД
     */
    abstract public function verHistory();

    /**
     * Основной метод класса. По очереди запускает методы обновления базы данных.
     *
     * @param string $oldV
     * @param string $newV
     * @final
     * @access public
     * @return boolean true если обновление прошло успешно
     */
    final public function runUpdates($oldV, $newV)
    {
        if (!is_string($oldV) || !is_string($newV)) {
            throw new \InvalidArgumentException("oldV and newV must be strings");
        }

        if (empty($oldV)) {
            $oldV = '0';
        }

        $updateMethods = $this->getUpdateMethodNames($oldV, $newV);

        $tr = $this->dbConnection->beginTransaction();
        // TODO: лучше, что бы транзакция была отдельно на каждую версию и после каждой версии сразу апдейтить конфиг
        try {
            // запускаем по очереди все обновления
            foreach ($updateMethods as $newV => $method) {
                ob_start();
                $this->$method();
                Yii::log(ob_get_clean(), 'trace', 'hamster.update.db');
                // занесли в журнал версию, до которой только что обновилась БД
                $this->logVersionIncrement($oldV, $newV);
                $oldV = $newV;
            }

            $tr->commit();

            // обрабатываем все сообщения в стеке лога
            $this->processLogStack();

            // обновляем версию БД в конфиге хомяка
            $config = \Config::load($this->moduleId); // конфиг, в котором лежит актуальная версия бд
            $config->dbVersion = $newV;
            $config->save();

            return true;
        } catch (\Exception $e) {
            $tr->rollback();
            \Yii::log($this->getTrace($e), 'error', 'hamster.update.db');
        }
        return false;
    }

    public function getTrace($e)
    {
        $traces = $e->getTrace();
        $msg = '';
        foreach ($traces as $trace) {
            if (isset($trace['file'], $trace['line']) && strpos($trace['file'], YII_PATH) !== 0) {
                $msg .= "\nin " . $trace['file'] . ' (' . $trace['line'] . ')';
                break; // остальные строки напечатает Yii
            }
        }
        return $e->getMessage() . $msg;
    }

    private function getUpdateMethodNames($oldV, $newV)
    {
        // выбираем методы для обновления
        $verHistory = $this->verHistory();
        $updateMethods = array();
        while (($ver = array_pop($verHistory))) {
            $ver = (string) $ver;
            if ($ver === $oldV) {
                break;
            }

            if (($curMethod = str_replace('.', '_', 'update' . $ver)) && method_exists($this, $curMethod)) {
                $updateMethods[$ver] = $curMethod;
            }

        }
        $updateMethods = array_reverse($updateMethods);

        return $updateMethods;
    }

    /**
     * Добавляет в стэк лога информацию об успешном обновлении БД
     *
     * @param mixed $old
     * @param mixed $new
     * @return void
     */
    private function logVersionIncrement($old, $new)
    {
        array_push($this->logStack, 'Успешное обновление базы данных модуля ' . $this->moduleId . ' (' . $old . '->' . $new . ')');
    }

    /**
     * Обрабатывает стэк сообщений лога
     *
     * @return void
     */
    private function processLogStack()
    {
        foreach ($this->logStack as $message) {
            \Yii::log($message, 'info', 'hamster.update.db');
        }
    }

    /**
     * вспомогательная функция в стиле Yii виджета.
     * Выполняет sql запросы, заключенные между {@link HUpdateDb::startRawSql()} и {@link HUpdateDb::endRawSql()}
     *
     * <pre>
     * Пример:
     *  $this->startRawSql();
     *  ?>
     *  SELECT * FROM `auth_user`;
     *  UPDATE `auth_user`(email) VALUES("test@test.com") WHERE `email`="netest@test.com";
     *  <?php
     *  $this->endRawSql();
     * </pre>
     *
     * @access protected
     * @see {@link HUpdateDb::endRawSql()}
     * @return void
     */
    protected function startRawSql()
    {
        $this->rawSqlStarted = true;
        ob_start();
    }

    /**
     * Вызывает {@link HUpdateDb::startRawSql()} если запрос в параметре $sql вернет хоть одну строку
     *
     * @param string $sql sql запрос-условие
     * @access protected
     * @return void
     */
    protected function startRawSqlIf($sql)
    {
        if ($this->dbConnection->createCommand($sql)->execute()) {
            $this->startRawSql();
        }

    }

    /**
     * Вызывает {@link HUpdateDb::startRawSql()} если запрос в параметре $sql вернет 0 строк
     *
     * @param string $sql sql запрос-условие
     * @access protected
     * @return void
     */
    protected function startRawSqlIfNot($sql)
    {
        if ($this->dbConnection->createCommand($sql)->execute() == 0) {
            $this->startRawSql();
        }

    }

    /**
     * Сотри: {@link HUpdateDb::startRawSql()}
     *
     * @access protected
     * @see {@link HUpdateDb::startRawSql()}
     * @return void
     */
    protected function endRawSql()
    {
        if ($this->rawSqlStarted) {
            $this->dbConnection->createCommand(ob_get_clean())->execute();
        }

        $this->rawSqlStarted = true;
    }
}
