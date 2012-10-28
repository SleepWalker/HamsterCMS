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
      
      return $route . '?' . http_build_query($params);
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
    
    //exception for standard yii module 'Gii'
    if($url[0] == 'gii') return false;
    
    if (count($url))
    {
      $modules = Yii::app()->modules;
      
      foreach($modules as $moduleId => $moduleConfig)
      {
        $moduleUrl = $moduleConfig['params']['moduleUrl'];
        // Массив с адресами всех модулей
        $moduleUrls[$moduleId] = $moduleUrl ? $moduleUrl : $moduleId;
      }
      // админский модуль пока живет сам по себе!
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
        $this->parseActionParams($controllerClass, $actionId, $actionParams);
        $route[] = strtolower($actionId);
        return implode('/', $route);
      }
    }
    return false;  // не применяем данное правило
  }
  
  /**
   * Проверяем последний параметр метода экшена на предмет наличия в нем регулярного выражения для парсинга оставшейся части url
   * @param string $controllerClass controller class name
   * @param string $actionId action id
   * @param array $urlParts array with unparsed part of url
   */
  function parseActionParams($controllerClass, $actionId, $urlParts)
  {      
    $urlParts = array_map('strtolower', $urlParts);
    $urlParts = array_values($urlParts);
    
    $methodName='action'.$actionId;
    $method=new ReflectionMethod($controllerClass, $methodName);
    if($method->getNumberOfParameters()>0)
    {
      $actionParams = $method->getParameters();
      $lastParam = array_pop($actionParams);
      
      // если у последнего параметра есть значение по умолчанию проверяем, нет ли там регулярного выражение для параметров
      if($lastParam->isDefaultValueAvailable() && $lastParam->getName() == '_pattern')
        $_pattern = $lastParam->getDefaultValue();
        
        // тут идет кусок скоращенного и немного переделанного кода конструктора CUrlRule
        // =========================
        if(preg_match_all('/<(\w+):?(.*?)?>/',$_pattern,$matches))
        {
          $tr['/']='\\/';
          $tokens=array_combine($matches[1],$matches[2]);
          foreach($tokens as $name=>$value)
          {
            if($value==='')
              $value='[^\/]+';
            $tr["<$name>"]="(?P<$name>$value)";
          }
        }
        
        $template=preg_replace('/<(\w+):?.*?>/','<$1>',$_pattern);
        $pattern='/^'.strtr($template,$tr).'$/u';
        
        if(YII_DEBUG && @preg_match($pattern,'test')===false)
          throw new CException(Yii::t('yii','The URL pattern "{pattern}" for action "{action}" is not a valid regular expression.',
            array('{action}'=>$actionId,'{pattern}'=>$pattern)));
        // =========================
        // конец куска кода CUrlRule
        
        // присваиваем массиву гет параметры, прошедшие валидацию
        if(preg_match($pattern, implode('/', $urlParts)))
          foreach($actionParams as $i => $param)
          {
            if(isset($urlParts[$i]))
              $_GET[$param->getName()] = $urlParts[$i];
          }
    }
  }
}
