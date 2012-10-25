<?php
/**
 * Api controller class
 * Used for ajax requests 
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.ShopController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class ApiController extends Controller
{
	/**
	 * Разбивает url на части и пытается найти, к какому модулю/контроллеру/действию пытаюстся получить доступ
	 * Вызывает из найденного класса метод ->api
	 * T!: парсинг для модулей, а так же придумать как должен вести себя Api, если запрос идет к определенному (не index) действию
	 */
	public function actionIndex()
	{
    if(!isset($_GET['path'])) $this->redirect('/');
    $path = explode('/', $_GET['path']);
    $controllerId = array_shift($path);
    $controller = ucfirst($controllerId) . 'Controller';
    Yii::import('application.controllers.' . $controller); // ставим класс на аутолоад
    if (@class_exists($controller))
    {
      $controller = new $controller($controllerId);
      $this->renderText(
        CJSON::encode( array(
          'content' => $controller->api($path),
        ))
      );
    }else
      throw new CHttpException(404,'Запрашиваемая страница не существует.');
	}
}
