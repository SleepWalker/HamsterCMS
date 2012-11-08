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
class UpdateController extends HAdminController 
{ 

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
  
	public function actionIndex()
  {
    $this->pageTitle = Yii::app()->name . ' - Обновление';
    $enModsIds = array_keys($this->enabledModules);
    $aliases = array(
      'application.components',
      'application.controllers',
      'application.extensions',
      'application.models',
      'application.vendors',
      'application.widgets',
    );
    $enModsIds[] = 'admin';
    foreach($enModsIds as $enModId)
      $aliases[] = 'application.modules.' . $enModId;

    $ans = $this->updatesHashList;

    $deleteList = array(); // файлы к удалению
    $updateList = array(); // файлы к обновлению
    foreach($aliases as $alias)
    {
      $arr = $this->hashDir($alias);
      if(!is_array($ans[$alias])) continue; // нету такого алиаса
      $deleteList = array_merge($deleteList, array_diff($arr['pathList'], $ans[$alias]['pathList']));
      $updateList = array_merge($updateList,
       array_diff(
         $ans[$alias]['hashList'],
         $arr['hashList'] 
       ));
    }

    ob_start();
?>
  <b style="color:red">К удалению:</b><br>
  <?php echo implode('<br>', $deleteList); ?>
  <p><b style="color:green">К обновлению:</b> <br>
  <?php echo implode('<br>', array_keys($updateList)); ?>
<?php
    echo '<br><br>' . CHtml::beginForm() . 
    CHtml::submitButton('Запустить обновление', array('name'=>'update')) .
    CHtml::endForm();
    $logMessage = ob_get_clean(); 

    if(isset($_POST['update']))
    {
      $status = true;
      $rootDir = Yii::getPathOfAlias('application');

      // загружаем обновления
      if(count($updateList))
        $status = $status && $this->getUpdates(array_keys($updateList));
      
      // удаляем старые файлы
      if(count($deleteList))
        foreach($deleteList as $file)
        {
          $fileToDelete = $rootDir . $file;
          if(is_dir($fileToDelete))
            $this->destroyDir($fileToDelete);
          elseif(file_exists($fileToDelete))
            $status = $status && unlink($fileToDelete);
        }
      // сохраняем новую карту директорий
      Yii::app()->cache->set('dirMap', $ans);

      // Пишем в лог
      Yii::log($logMessage, 'info', 'hamster.update');

      if($status == TRUE)
        Yii::app()->user->setFlash('success', 'Успешное обновление');
      else
        Yii::app()->user->setFlash('error', 'Во время обновления произошли ошибки');

      $this->refresh();
    }

    $this->renderText($logMessage);
  }

  protected function hashDir($alias) {
    // возвращаем значение из карты (в случае если она уже закеширована)
    if(is_array($this->dirMap[$alias])) return $this->dirMap[$alias];

    $dir = Yii::getPathOfAlias($alias);
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir),
      RecursiveIteratorIterator::CHILD_FIRST);
    $root = Yii::getPathOfAlias('application');
    foreach ($iterator as $file) {
      //if(substr($file->getBasename(), 0, 1) == '.' && $file->getBasename() != '.htaccess') continue; // пропускаем скрыте файлы (линукс)
      if($file->getBasename() == '.' || $file->getBasename() == '..') continue; 
        $path = str_replace($root, '', (string)$file);
        $pathList[] = $path;
      if ($file->isFile()) {
        $hashList[$path] = md5_file((string)$file); 
      }
    }
    return array(
      'pathList' => $pathList, // сюда входят и директории тоже
      'hashList' => $hashList,
    );
  }

  protected function getUpdatesHashList()
  {
    $curl = curl_init();
    
    curl_setopt_array($curl, array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_URL => 'http://www.update.hamstercms.com/',
      CURLOPT_USERAGENT => 'Hamster Updater',
    ));

    if(!($ans = curl_exec($curl)))
      throw new CHttpException(403,'Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));

    curl_close($curl);

    return unserialize($ans);
  }

  protected function getUpdates($fileList)
  {
    $curl = curl_init();
    $saveTo = Yii::getPathOfAlias('application.runtime') . DIRECTORY_SEPARATOR . 'update.zip';
    $extractTo = Yii::getPathOfAlias('application');
    
    curl_setopt_array($curl, array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_URL => 'http://www.update.hamstercms.com/',
      CURLOPT_USERAGENT => 'Hamster Updater',
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => array(
        'fileList' => serialize($fileList),
      )
    ));

    if(!($data = curl_exec($curl)))
      throw new CHttpException(403,'Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));

    curl_close($curl);

    file_put_contents($saveTo, $data);

    $zip = new ZipArchive;
    if ($zip->open($saveTo) === TRUE) {
      if($zip->extractTo($extractTo) === FALSE) return false;
      $zip->close();
      unlink($saveTo);
      return true;
    } else {
      return false;
    }
  }

  public function getDirMap()
  {
    if(!isset($this->_dirMap))
      $this->_dirMap = Yii::app()->cache->get('dirMap');
    return $this->_dirMap;
  }

  /**
   * Полностью удаляет содержимое $dir
   * @params string $dir путь к директории
   */
  function destroyDir($dir) 
  {
    if(!preg_match('%/$%', $dir)) $dir .= '/';
    $mydir = opendir($dir);

    while(false !== ($file = readdir($mydir))) {
      if($file != "." && $file != "..") {
        //chmod($dir.$file, 0777);
        if(is_dir($dir.$file)) {
          chdir('.');
          $this->destroyDir($dir.$file.'/');
          rmdir($dir.$file) or DIE("couldn't delete $dir$file<br />");
        }
        else
          unlink($dir.$file) or DIE("couldn't delete $dir$file<br />");
      }
    }

    rmdir($dir);
  }
}
