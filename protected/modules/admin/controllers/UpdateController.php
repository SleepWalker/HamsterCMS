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
    $aliases = array(
      'application.modules.admin',
      'application.modules.shop',
    );
    foreach($aliases as $alias)
    {
      $curDataStatus[$alias] = $this->hashDir(Yii::getPathOfAlias($alias));
    }

    $this->renderText('test');
	}

  protected function hashDir($dir) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir),
      RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $file) {
      if(substr($file->getBasename(), 0, 1) == '.' && $file->getBasename() != '.htaccess') continue; // пропускаем скрыте файлы (линукс)
        $pathList[] = (string)$file;
      if ($file->isFile()) {
        $hashList[md5_file((string)$file)] = (string)$file; 
      }
    }
    return array(
      'pathList' => $pathList,
      'hashList' => $hashList,
    );
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
