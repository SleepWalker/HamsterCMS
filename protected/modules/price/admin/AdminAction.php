<?php
/**
 * Admin action class for price module
 *
 * пример конфига прайсов (обязателен для работы модуля, должен находиться в application.config.priceConfig):
 *   return array(
 *     'gabbi' => array(
 *       // Идентификатор, который будет использоваться в 
 *       // базе данных (должен быть уникальным для каждого прайса)
 *       'id' => 1, 
 *       // Имя отображаемое пользователю в админке и в блоке фильтрации на сайте
 *       'name' => 'Габби',
 *       // строка прайса, с которой начинается список товаров (нумерация с нуля, естественно)
 *       'startRow' => 9,
 *       // массив настройки соответствия колонка БД => номер колонки прайса
 *       'columns' => array( 
 *         'code' => 0, // коды производителя (обязательны элемент, primaryKey)
 *         'name' => 1, // имя товара (обязательно)
 *         'price' => 3, // колонка с стоимостью
 *         // настройка категорий
 *         'cat' => array(
 *           // самый простой вариант категории. 
 *           // имя категории находится в одной строке с товаром в колонке под номером 4
 *           'brand' => 4, 
 *           'cat' => array(
 *           // ищем категории в строках, в которых все ячейки пустые, кроме ячейки в колонке 1
 *           // (случай, когда категория используется в качестве шапки-разделителя блоков таблицы с товарами)
 *             'row' => 1,
 *           ),
 *           // составная категория. 
 *           // это значит, что значение 5 колонки обьединится со значением первой колонки (разделитель - пробел)
 *           'cat2' => array(5,6), 
 *         ),
 *         // дополнительные колонки - это колонки, по которым не будет проводится фильтрация, 
 *         // но все же их можно добавить в бд и потом выводить в таблице на сайте (см. эл. массива extraLabels)
 *         'extra' => array(
 *           'size' =>  2,
 *         ),
 *       ),
 *     ),

 *     'odyagajko' => array(
 *       'id' => 2,
 *       'name' => 'Одягайко',
 *       'startRow' => 12,
 *       'columns' => array(...),
 *     ),

 *     ...

 *     // названия колонок (для стандартных типа name, code можно не указывать)
 *     // если не указать название для категории, то по умолчанию для всех категорий 
 *     // будет использоваться название "Категория"
 *     // id => label
 *     'attributeLabels' => array(
 *     ),
 *     // названия колонок из массива ['columns']['extra'], 
 *     // колонки, не указанные в этом массиве не будут выводится в таблице прайсов на сайте
 *     // id => label
 *     'extraLabels' => array(
 *       'size' => 'Размеры',
 *     ),
 *   );
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.price.admin.AdminAction
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
 
class AdminAction extends HAdminAction
{
  //TODO: страница настройки колонок таблицы, которая будет выводиться на сайте
  //TODO: страница добавления настроек для конкретного прайса (нужно сказать юзеру, что сначала надо настроить колонки)
  /**
   * @property string $runtimeDir путь к папке с файлами скриптов
   */
  public $runtimeDir;
  
  public function run()
  {    
    // import the module-level models and components
		$this->module->setImport(array(
			'price.models.*',
			'price.components.*',
		));
  }
  
  /**
	 * @return меню для табов
	 */
  public function tabs() {
    return array(
      ''  => 'Обновление прайсов',
    );
  }

  /**
   *  Загрузка и импорт прайсов
   */
  public function actionIndex() 
  {
    //TODO: модель для формы загрузки
    //TODO: сделать вьюхи
    ob_start();
    if(isset($_FILES['price']))
    {
      if(empty($_FILES['price']['name'][0]))
      {
        Yii::app()->user->setFlash('info', 'Вы забыли выбрать файл прайса');
      }else{
        if(!empty($_POST['priceSchema']))
        {

          $files = $_FILES['price'];
          foreach($files['tmp_name'] as $i => $tmpName)
          {
            $name = HPrice::getPricePath() . '/' . $files["name"][$i];
            // Сохраняем исходный файл в Uploads
            move_uploaded_file($tmpName, $name);        
            HPrice::load($name, $_POST['priceSchema'])->import();
          }
        }else
          Yii::app()->user->setFlash('info', 'Вы забыли выбрать схему прайса');
      }

      $this->refresh();
    }

    // пункты для выпадающего меню в форме
    $config = HPrice::getConfig();
    $priceSchema = array(
      '' => '-- Выберите схему прайсов --',
    );
    foreach($config as $priceId => $price)
      if(isset($price['name']))
        $priceSchema[$priceId] = $price['name'];

    echo '<h2>Загрузка новых прайсов</h2>';
    echo CHtml::beginForm('', 'post', array('enctype' => 'multipart/form-data'));
    echo '<p>' . CHtml::dropDownList('priceSchema', $_POST['file'], $priceSchema) . '</p><br />';
    echo 'Выберите прайсы (csv, xls, xlsx)'
      . CHtml::fileField('price[]', '', array(
        //'accept' => 'application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        //'multiple' => 'multiple',
      ))
      .'<p>'
      . CHtml::submitButton('Загрузить')
      . CHtml::endForm();
?>
<hr />
    <form action="" method="post" style="display:inline-block;text-align:center;">
<?php
    echo CHtml::submitButton('Очистить БД от всех прайсов', array('name'=>'flush'));
    echo ' ' . CHtml::submitButton('Очистить ФС от всех прайсов', array('name'=>'unlink'));
?>
    </form>
    <p>
      <ul>
        <li><b>Очистить БД от всех прайсов</b> - полностью очищает базу данных от содержимого прайсов. (использовать, когда нужно обновить прайсы в базе данных)</li>
        <li><b>Очистить ФС от всех прайсов</b> - полностью очищает файловую систему от прайсов.</li>
      </ul>
    </p>
<?php 
      if(isset($_POST['flush']))
        HPrice::refreshDb();
      if(isset($_POST['unlink']))
        foreach( new DirectoryIterator(HPrice::getPricePath()) as $file) 
        {
          if( $file->isFile() === TRUE && substr($file->getBasename(), 0, 1) != '.') 
            unlink($file->getPathname());
        }


    $this->renderText(ob_get_clean());
  } 
}
?>
