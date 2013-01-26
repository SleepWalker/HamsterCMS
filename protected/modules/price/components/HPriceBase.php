<?php
/**
 * Базовый класс для работы с прайсами
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hammster.mevalScripts.inc.price.HPriceBase
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class HPriceBase extends CComponent
{
  /**
   * @property string $file путь к файлу с прайсами
   */
  public $file;

  /**
   * @property string $columns настройки соответствия индекса колонки в прайсе колонке в бд
   */
  public $columns;

  /**
   * @property string $startRow номер строки с которой начинается прайс
   */  
  public $startRow;

  /**
   * @property integer $fileId ид файла, необходимый для идентификации записей прайсов в бд
   */
  public $fileId;

  /**
   * @property array $price массив с пропарсенным прайсом
   */
  protected $_price;

  /**
   * @property array $config массив с параметрами прайсов (application.config.priceConfig)
   */
  protected static $_config;

  protected function __construct($file)
  {
    $this->file = $file;
  }

  /**
   * Подгружает настройки прайсов, создает новый обьект прайсов и заполняет его информацией
   * 
   * @param string $file путь к файлу с прайсом
   * @param string $priceSchema идентификатор элемента массива настроек для парсинга прайсов, если не передать этот параметр, то вместо идентификатора будет взято имя файла
   * @static
   * @access public
   * @return HCPriceBase
   */
  public static function load($file, $priceSchema = false)
  {
    $instance = new self($file);

      $config = self::getConfig();

      if(!$priceSchema)
        $priceSchema = pathinfo ($file, PATHINFO_FILENAME);

      $instance->startRow = $config[$priceSchema]['startRow'];
      $instance->columns = $config[$priceSchema]['columns'];

    $instance->fileId = $config[$priceSchema]['id'];

    return $instance;
  }

  /**
   * Возвращает массив настроек
   * 
   * @static
   * @access public
   * @return string массив настроек или ошибку, в случае, если файл не найден
   */
  public static function getConfig()
  {
    $configFile = Yii::getPathOfAlias('application.config').'/priceConfig.php';

    if(file_exists($configFile))
    {
      if(!isset(self::$_config))
        self::$_config = require($configFile);
      return self::$_config;
    }else
      throw new CException('Отсутствует файл с настройками прайсов. Убедитесь, что файл с настройками доступен по адресу ' . $configFile);
  }

  /**
   * @static
   * @access public
   * @return string путь к директории, куда должны загружаться прайсы
   */
  public static function getPricePath()
  {
    $filePath = Yii::getPathOfAlias('webroot') . self::getPriceUrl();  
    if(!is_dir($filePath))
      mkdir($filePath);

    return $filePath;
  }

  /**
   * @static
   * @access public
   * @return string возвращает ссылку на папку, где хранятся файлы для скачки
   */
  public static function getPriceUrl()
  {
    return DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'price';
  }
  
  /**
   * Выводит содержимое файла с прайсами  
   * 
   * @access public
   * @return HPriceBase
   */
  public function show()
  {
    echo '<table>';
    foreach($this->price as $row)
    {
      echo '<tr><td>' . implode($row, '</td><td>') . '</td></tr>';
    }
    echo '</table>';

    return $this;
  }

  /**
   * Парсит файл прайсов
   * 
   * @access public
   * @return массив со строками прайса начиная со {@link $startRow} 
   */
  public function getPrice()
  {
    if(!isset($this->_price))
    {
      $ext = pathinfo($this->file, PATHINFO_EXTENSION);
      switch($ext)
      {
      case 'csv':
        $this->parseCsv();
        break;
      case 'xlsx':
      case 'xls':
        $this->parseExcel();
        break;
      }
    }
    return $this->_price;
  }

  /**
   * Парсит файл прайсов в формате *.csv
   * 
   * @access protected
   * @return void
   */
  protected function parseCsv()
  {
      $r = 0;
      if (($handle = fopen($this->file, "r")) !== FALSE) 
      {
        while (($data = fgetcsv($handle)) !== FALSE) 
        {
          if($r >= $this->startRow)
            for ($c=0; $c < count($data); $c++) 
            {
              $cellVal = trim($data[$c]);
              // если это цена - конвертим ее в float
              $this->_price[$r][$c] = preg_match('/^\d+([\.\,]\d+)?$/', $cellVal) 
                ? (float)round(str_replace(',', '.', $cellVal), 2)
                : $cellVal;
            }
          $r++;
        }
        fclose($handle);
      }
  }

  /**
   * Парсит файл прайсов в форматах MS Excel
   * 
   * @access protected
   * @return void
   */
  protected function parseExcel()
  {
    Yii::app()->controller->beginWidget('application.vendors.phpexcel.YPHPExcel');

    $inputFileName = $this->file;

    //Excel2007
    //Excel5

    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);

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
    $r = 0;
    //  Loop to read our worksheet in "chunk size" blocks 
    for ($startRow = 0; $startRow <= $totalRows; $startRow += $chunkSize)
    {
      //  Tell the Read Filter which rows we want this iteration 
      $chunkFilter->setRows($startRow, $chunkSize);
      //  Load only the rows that match our filter  
      $objPHPExcel = $objReader->load($inputFileName); 
      //    Do some processing here 
      $objWorksheet = $objPHPExcel->getActiveSheet();

      $tableArr = array();
      foreach ($objWorksheet->getRowIterator() as $row) 
      {
        if($r < $this->startRow) 
        {
          $r++;
          continue;
        }
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false); // This loops all cells,
        // even if it is not set.
        // By default, only cells
        // that are set will be
        // iterated.
        $c = 0;
        foreach ($cellIterator as $cell) 
        {
          $cellVal = trim($cell->getCalculatedValue());
          // если это цена - конвертим ее в float
          $this->_price[$r][$c] = $cellVal;
          $c++;
        }
        $r++;
      }
      $objPHPExcel->disconnectWorksheets();
      unset($objPHPExcel);
    }
    Yii::app()->controller->endWidget('YPHPExcel');
  }

  /**
   * Импортирует прайсы в базу данных
   * Перед импортом производится парсинг прайса исходя из 
   * настроек для конкретного файла в priceConfig.php
   * 
   * @access public
   * @return HPriceBase
   */
  public function import()
  {
    $withoutCatName = 'Без категории';

    foreach($this->price as $data)
    {
      // индикатор перехода на следующую строку (итерацию цикла)
      // используется в том случае, когда категория занимает всю строку прайса
      $nextRow = false;

      // обрабатываем категории
      foreach($this->columns['cat'] as $fieldId => $colId)
      {
        if(is_numeric($colId))
        {
          $cats[$fieldId] = $data[$colId];
        }elseif(is_array($colId)){
          if(isset($colId['row']))
          {
            // категория на всю строку
            // режим, когда категория находится в строке перед блоком относящихся к ней позиций
            if( 
              empty($data[$this->columns['code']]) || 
              empty($data[$this->columns['price']]) || 
              empty($data[$this->columns['name']])
            )
            {
              if(is_string($data[$colId['row']]))
                $cats[$fieldId] = $nextRow = $data[$colId['row']];
            }
          }else{
            // категория, собирающаяся из нескольких ячеек
            // генерируем название категории
            $catName = '';
            foreach($colId as $catCellId)
              $catName .= $data[$catCellId] . ' ';
            $cats[$fieldId] = ucfirst(strtolower(trim($catName)));
          }
        }

        if(empty($cats[$fieldId]))
          $cats[$fieldId] = $withoutCatName;
      }

      if($nextRow) continue;

      // дополнительные поля, по которым не будет проводиться поиск
      if(is_array($this->columns['extra']))
      {
        foreach($this->columns['extra'] as $index => $params)
          if(is_array($params))
            $extra[$index] = $data[$params['col']];
          else
            $extra[$index] = $data[$params];
      }
      else
        $extra = array();

      $array[] = array(
        'code' => $data[$this->columns['code']],
        'file_id' => $this->fileId,
        'cat' => $cats,
        'name' => $data[$this->columns['name']],
        'price' => (float)round(str_replace(',', '.', $data[$this->columns['price']]), 2),
        'extra' => serialize($extra),
      );
    }

    $this->queryArray($array);

    Yii::app()->user->setFlash('success', 'Успешный импорт прайса');
    Yii::app()->controller->refresh();

    return $this;
  }

  /**
   * Производит запросы в базу данных используя данные из массива,
   * полученного в результате парсинка в {@link HPriceBase::import}
   * 
   * @param array $array 
   * @access protected
   * @return HPriceBase
   */
  protected function queryArray(array $array)
  {
    $this->testDb();
    $connection=Yii::app()->db;
    $model = new Price;

    $item = reset($array);
    // Добавляем категории к колонкам
    foreach(array_keys($item['cat']) as $catId)
      $columns[] = $catId . '_id';

    unset($item['cat'], $catId);
    $columns = array_merge(array_keys($item), $columns);
    $columns = '`' . implode('`,`',$columns) . '`';

    // UPDATE QUERY
    $updateQuery = "UPDATE IGNORE `{$model->tableName()}` SET {updateValues} WHERE `{$model->primaryKey()}`={code};\n";
    //INSERT QUERY
    $insertQuery = "INSERT IGNORE INTO `{$model->tableName()}`(" . $columns . ") VALUES({insertValues});\n";

    //INSERT CATS QUERY
    $insertCatsQuery = "INSERT IGNORE INTO `{catTable}`(file_id, name) VALUES({$item['file_id']}, {insertValues});\n";

    // результирующий запрос
    $sqls = '';

    foreach($array as $item)
    {
      array_walk($item, function(&$value, $index) use ($connection) {
        if(!is_array($value)) //по сути условие, исключающее категории
          $value = $connection->pdoInstance->quote($value);
      });

      $curCats = $item['cat'];
      unset($item['cat']);
      foreach($curCats as $catId => $catName)
      {
        // делаем из категорий токен, что бы потом подставить ее id
        $item[$catId . '_id'] = '{' . $catId . $catName . '}';
        // имена категорий сгруппированные по их идентификатору (таблице)
        // в будущем эта информация будет использоваться для добавления категорий в таблицу
        $cats[$catId][$catName] = $connection->pdoInstance->quote($catName);
      }
      unset($curCats);

      $insertValues[] = implode(',',$item);

      $updateValues = $item;
      array_walk($updateValues, function(&$value, $index) {
        $value = '`' . $index . '`='.$value;
      });
      $updateValues = implode(',',$updateValues);

      $sqls .= strtr($updateQuery, array(
        '{updateValues}' => $updateValues,
        '{code}' => $item['code'],
      ));
      
      // обьединяем инсерты по 150 штук за запрос
      if(count($insertValues) == 150)
      {
        $sqls .= strtr($insertQuery, array(
          '{insertValues}' => implode('),(', $insertValues),
        ));
        $insertValues = array();
      }
    }

    // обьеденяем оставшиеся инсерты (это произойдет в том случае, если их осталось < 150)
    if(count($insertValues))
    {
      $sqls .= strtr($insertQuery, array(
        '{insertValues}' => implode('),(', $insertValues),
      ));
      $insertValues = array();
    }

    // создаем запрос для добавления категорий в соответствующие таблицы
    $catsSql = '';
    foreach($cats as $catId => $catNames)
    {
      $catsSql .= strtr($insertCatsQuery, array(
        '{insertValues}' => implode("),({$item['file_id']},", $catNames),
        '{catTable}' => 'price_' . $catId,
      ));
    }
    $connection->createCommand($catsSql)->execute();

    // получаем категории
    foreach(array_keys($cats) as $catId)
    {
      // переключаем модель на нужную нам таблицу
      $allCats = PriceCat::model('price_' . $catId)->findAll();
      foreach($allCats as $cat)
      {
        $catTockens['{' . $catId . $cat->name . '}'] = $cat->primaryKey;
      }
    }
    
    // Подставляем вместо токенов реальные id категорий
    $sqls = strtr($sqls, $catTockens);

    $connection->createCommand($sqls)->execute();

    return $this;
  }

  /**
   * Метод, восстанавливающий таблицы из дампа в случае,
   * если на этапе активации скрипта их не окажется 
   * 
   * @access public
   * @return void
   */
  protected function testDb()
  {
    // проверяем, есть ли все таблицы
    try{
      $db = Yii::app()->db;
      // запускаем sql комманды
      $db->createCommand('SHOW CREATE TABLE `price`')->execute();
    }catch(CDbException $e) {
      // одной из таблиц нету - ресетим таблицы
      self::refreshDb();
    }
  }

  /**
   * Очищает БД от старых таблиц и создает вместо них новые на основе текущего конфига.
   * 
   * @static
   * @access public
   * @return void
   */
  public static function refreshDb()
  {
    $db = Yii::app()->db;
    $config = self::getConfig();
    // в этот массив мы будем добавлять идентификаторы тех категорий, которые мы уже добавили в запрос
    $createdCats = array();
    ob_start();
?>
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

DROP TABLE IF EXISTS `price`;
CREATE TABLE IF NOT EXISTS `price` (
  `code` varchar(32) NOT NULL,
<?php
    // Создаем столбцы для внешних ключей категорий
    // так же создадим переменную с кодом для создания таблиц категорий
    $catTables = '';
    foreach($config as $item)
    {
      if(is_array($cats = $item['columns']['cat']))
        foreach(array_keys($cats) as $id)
        {
          if(in_array($id, $createdCats)) continue; // для этой категории уже добавлены строки в sql запрос
          echo "`{$id}_id` int(10) unsigned NOT NULL,\n";
          ob_start();
?>
DROP TABLE IF EXISTS `price_<?php echo $id; ?>`;
CREATE TABLE IF NOT EXISTS `price_<?php echo $id; ?>` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `file_id` int(10) unsigned,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `price_<?php echo $id; ?>_name` (`name`),
  KEY `file_id` (`file_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

<?php
          $catTables .= ob_get_clean();
          $createdCats[] = $id;
        }
    }
?>
  `file_id` int(10) unsigned NOT NULL,
  `name` varchar(256) NOT NULL,
  `price` decimal(19,2) unsigned NOT NULL,
  `extra` TEXT,
  PRIMARY KEY (`code`),
  KEY `cat_id` (`cat_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

<?php echo $catTables; ?>

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
<?php
    // создаем таблицу в БД
    $sql = ob_get_clean();
    $db->createCommand($sql)->execute();
    Yii::app()->user->setFlash('success', 'База данных успешно очищена');
  }
}
