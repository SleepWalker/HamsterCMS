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
    $enModsIds = array_keys($this->enabledModules);
    $enModsIds[] = 'admin';
    foreach($enModsIds as $enModId)
      $aliases[] = 'application.modules.' . $enModId;

    $aliases[] = 'application.components';

    $ans = $this->updatesHashList;

    $deleteList = array(); // файлы к удалению
    $updateList = array(); // файлы к обновлению
    foreach($aliases as $alias)
    {
      $curDataStatus[$alias] = $arr = $this->hashDir(Yii::getPathOfAlias($alias));
      if(!is_array($ans[$alias])) continue; // нету такого алиаса
      $deleteList = array_merge($deleteList, array_diff($arr['pathList'], $ans[$alias]['pathList']));
      $updateList = array_merge($updateList,
       array_diff(
         $ans[$alias]['hashList'],
         $arr['hashList'] 
       ));
    }

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
          $status = $status && unlink($rootDir . $file);

      if($status == TRUE)
        Yii::app()->user->setFlash('success', 'Успешное обновление');
      else
        Yii::app()->user->setFlash('error', 'Во время обновления произошли ошибки');

      $this->refresh();
    }

    ob_start();
?>
  <b style="color:red">К удалению:</b><br>
  <?php echo implode('<br>', $deleteList); ?>
  <p><b style="color:green">К обновлению:</b> <br>
  <?php echo implode('<br>', array_keys($updateList)); ?>
<?php
    echo '<p>' . CHtml::beginForm() . 
    CHtml::submitButton('Запустить обновление', array('name'=>'update')) .
    CHtml::endForm();

    $this->renderText(ob_get_clean());
  }

  protected function hashDir($dir) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir),
      RecursiveIteratorIterator::CHILD_FIRST);
    $root = Yii::getPathOfAlias('application');
    foreach ($iterator as $file) {
      if(substr($file->getBasename(), 0, 1) == '.' && $file->getBasename() != '.htaccess') continue; // пропускаем скрыте файлы (линукс)
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

  /**
   * Makes directory and returns BOOL(TRUE) if exists OR made.
   *
   * @param  $path Path name
   * @return bool
   */
  protected function rmkdir($path, $mode = 0755) {
    $path = rtrim(preg_replace(array("/\\\\/", "/\/{2,}/"), "/", $path), "/");
    $e = explode("/", ltrim($path, "/"));
    if(substr($path, 0, 1) == "/") {
      $e[0] = "/".$e[0];
    }
    $c = count($e);
    $cp = $e[0];
    for($i = 1; $i < $c; $i++) {
      if(!is_dir($cp) && !@mkdir($cp, $mode)) {
        return false;
      }
      $cp .= "/".$e[$i];
    }
    return @mkdir($path, $mode);
  }
}
