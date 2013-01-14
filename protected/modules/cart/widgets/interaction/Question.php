<?php
/**
 * Question widget class file
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.cart.widgets.interaction.Question
 * @version    1.0
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
Yii::import('application.modules.cart.widgets.interaction.Recall', true);
class Question extends Recall 
{

  /**
   * @property string $label надпись на ссылке
   */
  public $label = 'Задать вопрос';

  /**
   * @property string $route путь к контроллеру, обрабатывающему запросы
   */
  protected $route = 'cart/client/question';

  /**
   * @property string $cssClass цсс класс к которому будет привязываться событие
   */
  protected $cssClass = 'hCartQuestion';
}
