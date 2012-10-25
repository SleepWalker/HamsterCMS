<?php
/**
 * HModuleUrlRule class file
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.components.HModuleUrlRule
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class HModuleUrlRule extends CBaseUrlRule
{
  public $connectionID = 'db';
  
  /**
   * Creates a URL based on this rule.
   * @param CUrlManager $manager the manager
   * @param string $route the route
   * @param array $params list of parameters (name=>value) associated with the route
   * @param string $ampersand the token separating name-value pairs in the URL.
   * @return mixed the constructed URL. False if this rule does not apply.
   */
  public function createUrl($manager,$route,$params,$ampersand)
  {
    $routeParts = explode("/", $route);
    if($routeParts[0] == $routeParts[1]) // что-то на подобии admin/admin/action
    {
      unset($routeParts[0]); // удлаяем повторяющуюся часть из url
      $routeParts = array_values($routeParts);
      $route = implode("/", $routeParts);
      
      return $route . '/' . implode("/", $params);
    }
    
    return false;  // не применяем данное правило
  }

  /**
   * Parses a URL based on this rule.
   * @param CUrlManager $manager the URL manager
   * @param CHttpRequest $request the request object
   * @param string $pathInfo path info part of the URL (URL suffix is already removed based on {@link CUrlManager::urlSuffix})
   * @param string $rawPathInfo path info that contains the potential URL suffix
   * @return mixed the route that consists of the controller ID and action ID. False if this rule does not apply.
   */
  public function parseUrl($manager,$request,$pathInfo,$rawPathInfo)
  {
    $url = explode('/', $pathInfo);
    if (count($url))
    {
      Yii::import('application.modules.admin.models.Config', true);
      
      $modules = Config::hamsterModules();
      if(!is_array($modules['modules']) || !is_array($modules['enabledModules'])) return false;
      
      foreach($modules['enabledModules'] as $moduleId => $moduleAlias)
      {
        $moduleUrl = $modules['modules'][$moduleId]['params']['moduleUrl'];
        // Массив с адресами всех модулей
        $moduleUrls[$moduleId] = $moduleUrl ? $moduleUrl : $moduleId;
      }
      $moduleUrls['admin'] = 'admin';
      
      if(in_array($url[0], $moduleUrls)) // есть такой модуль
      {
        $getModuleByUrl = array_flip($moduleUrls);
        $moduleId = $getModuleByUrl[$url[0]];
        
        $route[] = $moduleId; // модуль
        if(!isset($url[1])) // index действия
          return $moduleId . '/' . $moduleId . '/index';
          

        $classFile = ucfirst($url[1]).'Controller.php';
        
        $moduleControllersDirectory = Yii::app()->basePath.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$moduleId.DIRECTORY_SEPARATOR.'controllers';
        
        // проверяем есть ли в url название контроллера
        if(is_file($moduleControllersDirectory.DIRECTORY_SEPARATOR.$classFile))
        {
          // в запросе есть название контроллера!
          $controllerId = $url[1];
          $actionParts = array_slice($url, 2);
        }
        else{
          $controllerId = $moduleId;
          $actionParts = array_slice($url, 1);
        }
        $route[] = $controllerId;
        $controllerClass = ucfirst($controllerId).'Controller';
          
        //работаем с {ModuleId}Controller
          
        if(!class_exists($controllerClass,false)) 
          Yii::import('application.modules.' . $moduleId . '.controllers.' . $controllerClass, true);
        
        // запускаем цикл, который будет искать методы в контроллере обьединяя части урл
        $actionParts = $actionParams = array_map('ucfirst', $actionParts);
        
        $actionId = implode($actionParts);
        while( !method_exists($controllerClass, 'action' . $actionId) )
        {
          if(count($actionParts) == 0) // осталось проверить только view действие
            if(!isset($url[2]) && method_exists($controllerClass, 'actionView')) //view действия
            {
              // если второй параметр в url - не controllerId, значит это viewUrl
              $_GET['id'] = $url[1];
              return $moduleId . '/' . $moduleId . '/view';
            }else{
              return false;
            }
          
          unset($actionParts[count($actionParts)-1]);
          $actionId = implode($actionParts);
        }

        $actionParams = array_diff($actionParams, $actionParts);
        $_GET['alias'] = strtolower(implode('/', $actionParams));
        $route[] = strtolower($actionId);
        return implode('/', $route);
      }
    }
    return false;  // не применяем данное правило
  }
}