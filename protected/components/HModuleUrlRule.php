<?php
/**
 * HModuleUrlRule
 *
 * @uses CBaseUrlRule
 * @package hamster.components.HModuleUrlRule
 * @version $id$
 * @copyright Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @author Sviatoslav Danylenko <mybox@udf.su>
 * @license PGPLv3 ({@link http://www.gnu.org/licenses/gpl-3.0.html})
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
    public function createUrl($manager, $route, $params, $ampersand)
    {
        $routeParts = explode("/", $route);

        //exception for standard yii module 'Gii'
        if ($routeParts[0] == 'gii') {
            return false;
        }

        if ($routeParts[0] == 'admin') {
            return false;
        }

        if (empty($route)) {
            // ссылка на главную
            return '';
        }

        if (count($routeParts) > 3 || count($routeParts) < 2) {
            // если это случилось, значит где-то ошибка FIXME (в будущем можно будет логировать и убрать этот баг для оптимизации системы)
            $message = get_class($this)."::createUrl(): Wrong params number (".implode(', ', $routeParts).")";
            Yii::log($message, CLogger::LEVEL_ERROR);
            throw new CException($message);
        }

        if (count($routeParts) == 3) {
            if ($routeParts[0] == $routeParts[1]) {
                // что-то на подобии admin/admin/action
                unset($routeParts[0]); // удлаяем повторяющуюся часть из url
                $routeParts = array_values($routeParts);
            }

            // узнаем, изменен ли у модуля не стандартный url
            if (isset(Yii::app()->modules[$routeParts[0]]['params']['moduleUrl'])) {
                $routeParts[0] = Yii::app()->modules[$routeParts[0]]['params']['moduleUrl'];
            }

        }

        if (isset($routeParts[1]) && $routeParts[1] == 'view') {
            // если это действие actionview - убираем его из url
            unset($routeParts[1]); // удлаяем view часть из url
            // Присоединяем к урл ид модели
            $urlExtra[] = array_shift($params);
        } elseif ($route == 'page/index' && !count($params)) {
            //FIXME: временное условие для ссылок на главную страницу сайта
            $routeParts = array();
        } else {
            if (end($routeParts) == 'index') {
                // индекс нам в урл не нужен
                array_pop($routeParts);
            }

            $methodParams = $this->getActionParamsByRoute($route);

            //TODO: добавить парсинг по регулярным выражениям
            if ($methodParams) {
                foreach ($methodParams as $mparam) {
                    if (isset($params[$mparam->getName()])) {
                        $urlExtra[] = $params[$mparam->getName()];
                        unset($params[$mparam->getName()]);
                    }
                }
            }
        }

        $url = implode("/", $routeParts);

        if (isset($urlExtra) && count($urlExtra)) {
            $url .= '/' . implode("/", $urlExtra);
        }

        //дополнительные параметры-частички url которые пишутся через слеш
        $url .= count($params) ? '?' . http_build_query($params) : '';

        if (Yii::app()->params['i18n']['enabled'] == true) {
            $language = isset(Yii::app()->request->cookies['myLang']) ? Yii::app()->request->cookies['myLang']->value : '';
            if ($language != Yii::app()->sourceLanguage) {
                $url = $language . '/' . $url;
            }

        }

        return $url;
    }

    /**
     * Parses a URL based on this rule.
     * @param CUrlManager $manager the URL manager
     * @param CHttpRequest $request the request object
     * @param string $pathInfo path info part of the URL (URL suffix is already removed based on {@link CUrlManager::urlSuffix})
     * @param string $rawPathInfo path info that contains the potential URL suffix
     * @return mixed the route that consists of the controller ID and action ID. False if this rule does not apply.
     */
    public function parseUrl($manager, $request, $pathInfo, $rawPathInfo)
    {
        $url = explode('/', strtolower($pathInfo));

        //exception for standard yii module 'Gii'
        if ($url[0] == 'gii') {
            return false;
        }

        if (count($url)) {
            $modules = Yii::app()->modules;
            $moduleUrls = array();
            foreach ($modules as $moduleId => $moduleConfig) {
                if (isset($moduleConfig['params']['aliases'][$pathInfo]['route'])) {
                    // Новый вариант роутинга. когда все пути задаются в админке
                    return $moduleId . '/' . $moduleConfig['params']['aliases'][$pathInfo]['route'];
                }

                // проверяем view url
                $pathInfoWithId = preg_replace('/[^\/]+$/', '{id}', $pathInfo);
                if (isset($moduleConfig['params']['aliases'][$pathInfoWithId]['route'])) {
                    // Новый вариант роутинга. когда все пути задаются в админке ({id} placeholder)
                    $_GET['id'] = end($url);
                    return $moduleId . '/' . $moduleConfig['params']['aliases'][$pathInfoWithId]['route'];
                }

                // Массив с адресами всех модулей
                if (!isset($moduleConfig['params']['aliases'])) {
                    $moduleUrls[$moduleId] = isset($moduleConfig['params']) && !empty($moduleConfig['params']['moduleUrl']) ? $moduleConfig['params']['moduleUrl'] : $moduleId;
                }

            }

            if (!in_array($url[0], $moduleUrls)) {
                // нет такого модуля
                return false;
            }

            $getModuleByUrl = array_flip($moduleUrls);
            $moduleId = $getModuleByUrl[$url[0]];
            $module = \Yii::app()->getModule($moduleId);

            if (!$module) {
                throw new \CException('Wrong url format');
            }

            $route[] = $moduleId; // модуль
            if (!isset($url[1])) {
                // index действия для случаев, когда у контроллера и модуля одинаковый id (admin/admin/index)
                return $moduleId . '/' . $moduleId . '/index';
            }

            // TODO: в этом месте могут не проходить контроллеры на подобии ShareCountController. из-за нескольких смен регистра в имени
            // можно пофиксить этот момент разве что с помощью ручного поиска в фс
            $classFile = ucfirst($url[1]) . 'Controller.php';

            $moduleControllersDirectory = Yii::getPathOfAlias('application.modules.' . $moduleId . '.controllers');

            // проверяем есть ли в url название контроллера
            if (is_file($moduleControllersDirectory . DIRECTORY_SEPARATOR . $classFile)) {
                // в запросе есть название контроллера!
                $controllerId = $url[1];
                $actionParts = array_slice($url, 2);
            } else {
                $controllerId = $moduleId;
                $actionParts = array_slice($url, 1);
            }
            $route[] = $controllerId;

            // индекс дейтвия moduleId/controllerId/index
            if (count($actionParts) == 0) {
                return implode('/', $route);
            }

            //работаем с {xxx}Controller
            if (isset($module->controllerMap[$controllerId])) {
                $controllerClass = $module->controllerMap[$controllerId];
            } else {
                $controllerClass = ucfirst($controllerId) . 'Controller';

                if (isset($module->controllerNamespace)) {
                    $controllerClass = $module->controllerNamespace . '\\' . $controllerClass;
                } elseif (!class_exists($controllerClass, false)) {
                    Yii::import('application.modules.' . $moduleId . '.controllers.' . $controllerClass, true);
                }
            }

            // запускаем цикл, который будет искать методы в контроллере обьединяя части урл
            $actionParts = $actionParams = array_map('ucfirst', $actionParts);

            $actionId = implode($actionParts);
            while (!method_exists($controllerClass, 'action' . $actionId)) {
                // осталось проверить только view действие
                if (count($actionParts) == 0) {
                    if (method_exists($controllerClass, 'actionView')) {
                        if (!isset($url[2])) {
                            // module/module/view  + id параметр
                            $_GET['id'] = $url[1];
                            return $moduleId . '/' . $moduleId . '/view';
                        } elseif (!isset($url[3]) && $url[0] != $url[1]) {
                            // module/controller/view + id параметр
                            $_GET['id'] = $url[2];
                            return $moduleId . '/' . $controllerId . '/view';
                        }
                    }
                    return false;
                }

                unset($actionParts[count($actionParts) - 1]);
                $actionId = implode($actionParts);
            }

            $actionParams = array_diff($actionParams, $actionParts);
            $this->parseActionParams($controllerClass, $actionId, $actionParams);
            $route[] = strtolower($actionId);
            return implode('/', $route);
        }
        return false; // не применяем данное правило
    }

    /**
     * Проверяем последний параметр метода экшена на предмет наличия в нем регулярного выражения для парсинга оставшейся части url
     *
     * @param string $controllerClass
     * @param string $actionId
     * @param array $urlParts
     * @access private
     * @return void
     */
    private function parseActionParams($controllerClass, $actionId, $urlParts)
    {
        $urlParts = array_map('strtolower', $urlParts);
        $urlParts = array_values($urlParts);

        $actionParams = $this->getActionParams($controllerClass, $actionId);
        if ($actionParams) {
            $lastParam = end($actionParams);

            // если у последнего параметра есть значение по умолчанию проверяем, нет ли там регулярного выражение для параметров
            if ($lastParam->isDefaultValueAvailable() && $lastParam->getName() == '_pattern') {
                array_pop($actionParams); // удаляем элемент _pattern
                $_pattern = $lastParam->getDefaultValue();

                // тут идет кусок сокращенного и немного переделанного кода конструктора CUrlRule
                // =========================
                if (preg_match_all('/<(\w+):?(.*?)?>/', $_pattern, $matches)) {
                    $tr['/'] = '\\/';
                    $tokens = array_combine($matches[1], $matches[2]);
                    foreach ($tokens as $name => $value) {
                        if ($value === '') {
                            $value = '[^\/]+';
                        }

                        $tr["<$name>"] = "(?P<$name>$value)";
                    }
                }

                $template = preg_replace('/<(\w+):?.*?>/', '<$1>', $_pattern);
                $pattern = '/^' . strtr($template, $tr) . '$/u';

                if (YII_DEBUG && @preg_match($pattern, 'test') === false) {
                    throw new CException(Yii::t('yii', 'The URL pattern "{pattern}" for action "{action}" is not a valid regular expression.', array(
                        '{action}' => $actionId,
                        '{pattern}' => $pattern,
                    )));
                }

                // =========================
                // конец куска кода CUrlRule

                // проводим валидацию параметров
                if (!preg_match($pattern, implode('/', $urlParts))) {
                    return false;
                }

            }

            // Добавляем параметры в $_GET
            foreach ($actionParams as $i => $param) {
                if (isset($urlParts[$i])) {
                    $_GET[$param->getName()] = $urlParts[$i];
                }

            }
        }
    }

    /**
     * @param string $controllerClass имя класса контроллера
     * @param string $actionId идентификатор экшена
     * @return array $actionParams массив обьектов с информацией о параметрах метода действия
     */
    private function getActionParams($controllerClass, $actionId)
    {
        $methodName = 'action' . ucfirst($actionId);
        $method = new \ReflectionMethod($controllerClass, $methodName);
        $actionParams = null;
        if ($method->getNumberOfParameters() > 0) {
            $actionParams = $method->getParameters();
        }

        return $actionParams;
    }

    private function getActionParamsByRoute($route)
    {
        // TODO: как-то не очень разумно инклюдить контроллеры для парсинга параметров урлов. нужно это переписать
        $route = explode('/', $route);
        try {
            if (count($route) == 3) {
                // модули
                list($moduleId, $controllerId, $actionId) = $route;
                $module = \Yii::app()->getModule($moduleId);
                $controllerClass = ucfirst($controllerId) . 'Controller';
                if ($module && isset($module->controllerMap[$controllerId])) {
                    $controllerClass = $module->controllerMap[$controllerId];
                } elseif ($module && isset($module->controllerNamespace)) {
                    $controllerClass = $module->controllerNamespace . '\\' . $controllerClass;
                } else {
                    $controllerClass = Yii::import('application.modules.' . $moduleId . '.controllers.' . $controllerClass, true);
                }
            } elseif (count($route) == 2) {
                // Обычные контроллеры
                $controllerClass = Yii::import('application.controllers.' . ucfirst($route[0]) . 'Controller', true);
                $actionId = $route[1];
            }

            return $this->getActionParams($controllerClass, $actionId);
        } catch (Exception $e) {
            return false;
        }
    }
}
