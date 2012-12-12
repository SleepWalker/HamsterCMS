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
abstract class HUpdateDb
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
   * @property array $updateMethods названия методов обновления
   */
  protected $updateMethods = array();
  
  /**
   * Инициализирует свойства класса
   * 
   * @param mixed $moduleId 
   * @final
   * @access protected
   * @return void
   */
  protected final function __construct($moduleId)
  {
    $this->moduleId = $moduleId;
    $this->c = Yii::app()->db;
    
    // подключаем модели модуля
    Yii::app()->controller->module->setImport(array(
			$moduleId.'.models.*',
    ));
    
    // проверяем все ли методы для обновления у нас есть
    $verHistory = $this->verHistory();
    unset($verHistory[0]); // первый элемент убираем, так как это версия, с которой приложение начало свое существование
    foreach($verHistory as $ver)
      if(($curMethod = str_replace('.', '_', 'update' . $ver)) && !method_exists($this, str_replace('.', '_', 'update' . $ver)))
        throw new CException("Отсутствует метод для обновления на версию {$ver}");
      else
        // добавляем функции, которые будут вызываться в методе {@link HUpdateDB::update()}
        $this->updateMethods[$ver] = $curMethod; 
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
   * @param mixed $oldV 
   * @param mixed $newV 
   * @final
   * @access public
   * @see {@link HUpdateDb::$updateMethods}
   * @return boolean true если обновление прошло успешно
   */
  public final function update($oldV, $newV)
  {
    if(empty($oldV)) $oldV = 0;

    $tr = $this->c->beginTransaction();
    try
    {
      // запускаем по очереди все обновления
      foreach($this->updateMethods as $newV => $method)
      {
        $this->$method();
        // занесли в журнал версию, до которой только что обновилась БД
        $this->logPush($oldV, $newV);       
        $oldV = $newV;
      }
      
      $tr->commit();
      
      // обрабатываем все сообщения в стеке лога
      $this->log();
      
      // обновляем версию БД в конфиге хомяка
      $config = Config::load($this->moduleId); // конфиг, в котором лежит актуальная версия бд
      $config->dbVersion = $newV;
      $config->save();
      
      return true;
    }
    catch(Exception $e) {
      $tr->rollback();
      Yii::log($e->getMessage(), 'error', 'hamster.update.db');
    }
    return false;
  }
  
  /**
   * Добавляет в стэк лога информацию об успешном обновлении БД
   * 
   * @param mixed $old 
   * @param mixed $new 
   * @access protected
   * @return void
   */
  protected function logPush($old, $new)
  {
    array_push($this->logStack, 'Успешное обновление базы данных модуля ' . $this->moduleId . ' (' . $old . '->' . $new . ')');
  }
  
  /**
   * Обрабатывает стэк сообщений лога  
   * 
   * @access protected
   * @return void
   */
  protected function log()
  {
    foreach($this->logStack as $message)
    {
      Yii::log($message, 'info', 'hamster.update.db');
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
    ob_start();
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
    $this->c->createCommand(ob_get_clean())->execute();
  }
}
