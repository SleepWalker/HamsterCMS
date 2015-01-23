<?php
/**
 * YPHPExcel вспомогательный класс для интеграции PHPExcel в Yii. Он всего-лишь отключает автозагрузчик Yii и выключает его включает его обратно, после того, как вызывается метод ->end().
 * Так же в этом файле находится вспомогательный класс для итерации по Excel файлам.
 *
 * Пример:
 * $this->beginWidget('application.vendors.phpexcel.YPHPExcel');
 * ...
 * // операции с PHPExcel
 * ...
 * $this->endWidget('YPHPExcel');
 * 
 * @uses CWidget
 * @package hamster.vendors.phpexcel.YPHPExcel
 * @version $id$
 * @copyright Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @author Sviatoslav Danylenko <mybox@udf.su> 
 * @license PGPLv3 ({@link http://www.gnu.org/licenses/gpl-3.0.html})
 */
class YPHPExcel extends CWidget
{
  //TODO: создать методы для часто выполняемых задач
  /**
   * Возвращаем обратно автолоадер Yii
   * 
   * @access public
   * @return void
   */
  public function run()
  {
    spl_autoload_register(array('YiiBase','autoload'));
  }
}

spl_autoload_unregister(array('YiiBase','autoload'));
$phpExcelPath = Yii::getPathOfAlias('application.vendors.phpexcel');
include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');

/**  Define a Read Filter class implementing PHPExcel_Reader_IReadFilter  */ 
class chunkReadFilter implements PHPExcel_Reader_IReadFilter 
{ 
    private $_startRow = 0; 
    private $_endRow   = 0; 

    //  Set the list of rows that we want to read  
    public function setRows($startRow, $chunkSize) { 
        $this->_startRow = $startRow; 
        $this->_endRow   = $startRow + $chunkSize; 
    } 

    public function readCell($column, $row, $worksheetName = '') { 
        //  Only read the heading row, and the configured rows 
        if (($row == 1) ||
            ($row >= $this->_startRow && $row < $this->_endRow)) { 
            return true; 
        } 
        return false; 
    } 
} 
