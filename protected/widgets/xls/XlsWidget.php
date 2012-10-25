<?php	
/**
 * Widget for managing prices
 * Provides parsing, storing, displaying and sorting of .csv files
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.ShopController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
Yii::import('application.vendors.phpexcel.PHPExcel',true);  
Yii::import('application.widgets.xls.models.*'); 

class XlsWidget extends CWidget
{  
  protected $priceSchema = array(
    'ECKO.csv' => array(
      'code' => 1,
      'product' => 2,
      'dealer_price' => 3,
      'sale_price' => 4,
      'brand' => 5,
      'cat' => array(6, 7),
    ),
    'ERC.csv' => array(
      'code' => 4,
      'product' => 3,
      'dealer_price' => 6,
      'sale_price' => 5,
      'brand' => 0,
      'cat' => array(2),
    ),
  );
  public function init()
  {    
    if($_GET['parse'])
    {
      $this->parse($_GET['file']);
      $this->controller->redirect(array('price/gen', 'file'=>$_GET['file']));
    }
    
    if($_GET['import'])
    {
      $this->dbImport($_GET['file']);
      $this->controller->redirect(array('price/gen', 'file'=>$_GET['file']));
    }
    
    if($_GET['show'])
    {
      $this->showTable($_GET['file']);
    }
    
    if($_GET['log'])
    {
      $this->showLog($_GET['file']);
    }
    
    if($_GET['flush'])
    {
      $this->flushBd();
      $this->controller->redirect(array('price/gen'));
    }
  }
  
  /**
   *  Парсит файл прайсов
   *  @param $fileName имя файла прайсов
   */
  public function parse($fileName)
  {
    $workDir = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'priceImport'.DIRECTORY_SEPARATOR;
    $inputFileName = $workDir.$fileName;
    $inputFileType=PHPExcel_IOFactory::identify($inputFileName);
      
    //  Create a new Reader of the type defined in $inputFileType 
    $objReader = PHPExcel_IOFactory::createReader($inputFileType); 


    //  Define how many rows we want to read for each "chunk" 
    // чем больше частичка - тем быстрее выполняется скрипт, но тем больше оперативки он жрет
    $chunkSize = 2048; 
    // Create a new Instance of our Read Filter  
    $chunkFilter = new chunkReadFilter(); 

    // Tell the Reader that we want to use the Read Filter 
    //$objReader->setReadFilter($chunkFilter); 
    
     $totalRows = count(file($inputFileName));
    //  Loop to read our worksheet in "chunk size" blocks 
    for ($startRow = 0; $startRow <= $totalRows; $startRow += $chunkSize)
    {
        //  Tell the Read Filter which rows we want this iteration 
        $chunkFilter->setRows($startRow, $chunkSize);
        //  Load only the rows that match our filter  
        $objPHPExcel = $objReader->load($inputFileName); 
        //    Do some processing here 
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $r = 0;
        $tableArr = array();
        foreach ($objWorksheet->getRowIterator() as $row) 
        {
          $cellIterator = $row->getCellIterator();
          $cellIterator->setIterateOnlyExistingCells(false); // This loops all cells,
                                                             // even if it is not set.
                                                             // By default, only cells
                                                             // that are set will be
                                                             // iterated.
          $c = 0;
          foreach ($cellIterator as $cell) 
          {
            $cellVal = trim($cell->getValue());
            // если это цена - конвертим ее в float
            if(preg_match('/^\d+[\.\,]\d+$/', $cellVal) || preg_match('/^\d+$/', $cellVal)) $cellVal = (float)str_replace(',', '.', $cellVal);
            $tableArr[$r][$c] = $cellVal;
            $c++;
          }
          $r++;
        }
        $objPHPExcel->disconnectWorksheets();
        unset($objPHPExcel);
    }
    
    Yii::app()->cache->set('xls.'.$fileName, $tableArr); 
    /*echo '<pre>';
    print_r($tableArr);
    echo '</pre>';*/
  }
  
  /**
   *  Импортирует данные из временной таблицы в базу данных
   *  @param $fileName имя файла прайсов
   */
  protected function dbImport($fileName)
  {
    $errors = array();
    $workDir = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'priceImport'.DIRECTORY_SEPARATOR;
    $tableArr = Yii::app()->cache['xls.'.$fileName];
    array_shift($tableArr); // удаляем шапку
    $cellId = $this->priceSchema[$fileName];
    foreach($tableArr as $row)
    {
      // Получаем инфу о бренде
      $brandName = $row[$cellId['brand']];
      if(empty($brandName)) $brandName = 'Не известный производитель';
      if(empty($brandsArr[$brandName]))
      {
        // добавляем/добываем новый бренд
        $brand = new XlsBrand;
        $brand->name = $brandName;
        if(!$brand->save())
        {
          // если модель не валидная - значит либо имя слишком длинное, либо такой бренд уже есть
          // будем считать, что у нас второй случай
          // ищем бренд с таким же именем
          
          // но все же сперва для отчетности на всякий случай сейвим ошибки
          $modelErrors = $brand->errors;
          unset($modelErrors['name']);
          if(count($modelErrors))
            array_push($errors, 'Ошибка в модели: XlsCategorie', $modelErrors, $row);
          
          $brand = XlsBrand::model()->findByAttributes(array('name'=>$brandName));
        }
        // сохраняем id бренда в массиве, что бы не делать кучу запросов
        $brandsArr[$brandName] = $brand->primaryKey;
      }
      $brandId = $brandsArr[$brandName];
      
      // генерируем название категории
      foreach($cellId['cat'] as $catCellId)
        $catName .= $row[$catCellId] . ' ';
      $catName = ucfirst(strtolower(trim($catName)));
      $catName = $row[$cellId['cat'][0]];
      if(empty($catName)) $catName = 'Без категории';
      if(empty($catsArr[$catName]))
      {
        // добавляем/добываем новую запись
        $cat = new XlsCategorie;
        $cat->name = $catName;
        if(!$cat->save())
        {
          // если модель не валидная - значит либо имя слишком длинное, либо такая запись уже есть
          // будем считать, что у нас второй случай
          // ищем запись с таким же именем
          
          // но все же сперва для отчетности на всякий случай сейвим ошибки
          $modelErrors = $cat->errors;
          unset($modelErrors['name']);
          if(count($modelErrors))
            array_push($errors, 'Ошибка в модели: XlsCategorie', $modelErrors, $row);
          
          $cat = XlsCategorie::model()->findByAttributes(array('name'=>$catName));
        }
        
        // сохраняем id записи в массиве, что бы не делать кучу запросов
        $catsArr[$catName] = $cat->primaryKey;
      }
      $catId = $catsArr[$catName];
      
      // Добавляем товар
      if($row[$cellId['code']] == '' || empty($catId) || empty($brandId))
      {
        // производитель - негодяй и не указал код товара, а ведь без него нельзя...
        // добавляем этот код в массив для отчетности
        array_push($errors, "!!!Не указан ID ({$row[1]}) или категория ($catId) или бренд ($brandId) !!!", $row);
        
        // товар мы естественно не добавляем
        continue;
      }
      $xlsProd = new XlsProd('add');
      $xlsProd->id = $row[$cellId['code']];
      $xlsProd->cat_id = $catId;
      $xlsProd->brand_id = $brandId;
      $xlsProd->name = $row[$cellId['product']];
      $xlsProd->dealer_price = (float)$row[$cellId['dealer_price']];
      $xlsProd->sale_price = (float)$row[$cellId['sale_price']];
      if(!$xlsProd->save())
      {            
        // сохраняем инфу об ошибках
        array_push($errors, 'Ошибка в модели: xlsProd', $xlsProd->errors, $row);
      }
    }
    // фух! мы спарсили всю таблицу, сохраним все в лог и вывидем его на экран
    ob_start();
    print_r($errors);
    $log = ob_get_clean();
    //echo '<pre>' . $log . '</pre>';
    file_put_contents($workDir.$fileName . '.log', $log);
  }
  
  /**
   *  Выводит на экран данные из временной таблицы прайсов
   *  @param $fileName имя файла прайсов
   */
  public function showTable($fileName)
  {
    $tableArr = Yii::app()->cache['xls.'.$fileName];
	  
    echo '<table>' . "\n";
    foreach($tableArr as $row)
    {
      echo '<tr>' . "\n";

      foreach($row as $cell)
      {
        echo '<td>' . $cell . '</td>' . "\n";
      }
      echo '</tr>' . "\n";
    }
    echo '</table>' . "\n";
  }
  
  /**
   *  Очищает всю базу данных путем удаления таблиц и создания новых
   */
  protected function flushBd()
  {
    $sql= file_get_contents(Yii::getpathOfAlias('application.widgets.xls').DIRECTORY_SEPARATOR.'shema.sql');
    $connection=Yii::app()->db; 
    $command=$connection->createCommand($sql);
    $rowCount=$command->execute();
  }
  
  /**
   *  Показывает содержимое логов импорта в бд
   *  @param $fileName имя файла прайсов
   */
  protected function showLog($fileName)
  {
    $workDir = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'priceImport'.DIRECTORY_SEPARATOR;
    $log = file_get_contents($workDir.DIRECTORY_SEPARATOR.$fileName.'.log');
    echo '<pre>' . $log . '</pre>';
  }
  
  public function getPriceArr($fileName)
  {
    return Yii::app()->cache['xls.'.$fileName];
  }
  
  public function savePriceArr($fileName, $tableArr)
  {
    Yii::app()->cache->set('xls.'.$fileName, $tableArr);
  }
}
  
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


