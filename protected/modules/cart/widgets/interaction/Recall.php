<?php
/**
 * Recall widget class file
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.cart.widgets.interaction.Recall
 * @version    1.0
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Recall extends CWidget 
{
  public $htmlOptions = array();

  /**
   * @property string $label надпись на ссылке
   */
  public $label = 'Перезвоните мне';

  /**
   * @property string $route путь к контроллеру, обрабатывающему запросы
   */
  protected $route = 'cart/client/recall';

  /**
   * @property string $cssClass цсс класс к которому будет привязываться событие
   */
  protected $cssClass = 'hCartRecallMe';

  /**
   * @property Shop $model модель товара, если не указана, в письме не должна выводиться ссылка на источник запроса
   */
  public $model = false;

  public function run() 
  {
    $urlParams = $this->model ? array('id' => $this->model->primaryKey) : array();

    $this->htmlOptions['class'] = $this->cssClass . ' ' . $this->htmlOptions['class'];

    echo CHtml::link($this->label, Yii::app()->createUrl($this->route, $urlParams), $this->htmlOptions);

    $this->widget('application.widgets.juiajaxdialog.AjaxDialogWidget', array(
      'selectors' => array('.'.$this->cssClass),
      'options' => array(
        'title' => $this->label,
      ),
    ));
  }
}
