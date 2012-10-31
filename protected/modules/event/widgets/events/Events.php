<?php	
/**
 * Виджет Events 
 * Строит информер с постами
 * Описание параметров ниже
 * 
 * @uses CWidget
 * @package hamster.modules.event.widgets.events.Events
 * @version $id$
 * @copyright Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @author Sviatoslav Danylenko <mybox@udf.su> 
 * @license PGPLv3 ({@link http://www.gnu.org/licenses/gpl-3.0.html})
 */
class Events extends CWidget
{
  // количество колонок с товарами
  public $cols = 1;
  
  // количество строк с товарами
  public $rows = 1;
  
  // сколько товаров показывать (если так и останется false, то значение переопределится в init() )
  public $amount = false;
  
  // вьюха для отображения товара
  public $view = '_event';
  
  public function init()
  {
    Yii::import('event.models.*');
    
    if (!$this->amount) $this->amount = $this->cols * $this->rows;
     
    // создаем провайдера в зависимости от переданных параметров
    $dataProvider = $this->createProvider();
    
    // рендерим мероприятия
    $this->renderEvents($dataProvider);
  }
  
  // protected createProvider() {{{ 
  /**
   * Создает обьект CActiveDataProvider в зависимости от параметров, переданных виджету
   * 
   * @access protected
   * @return CActiveDataProvider
   */
  protected function createProvider()
  {    
    return new CActiveDataProvider('Event', array(
      'criteria' => array(
        'limit' => $this->amount,
      ),
      'pagination' => false,
    ));
  }
  // }}}
  
  // protected renderEvents(dataProvider) {{{ 
  /**
   * renderEvents
   * 
   * @param CActiveDataProvider $dataProvider 
   * @access protected
   * @return void
   */
  protected function renderEvents($dataProvider) {
    $events = $dataProvider->data;
    foreach($events as $event)
    {
      $this->render($this->view, array(
        'cols' => $this->cols,
        'rows' => $this->rows,
        'data' => $event,
      ));
    }
  }
  // }}}
}
