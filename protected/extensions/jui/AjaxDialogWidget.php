<?php	
/**
 * Extends CJuiWidget for using it with ajax requests
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.ext.jui.AjaxDialogWidget
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
Yii::import('zii.widgets.jui.CJuiWidget');

class AjaxDialogWidget extends CJuiWidget
{
  // селектор елемента родителя в котором нужно будет искать по селекторам $selectors      
  public $parentSel = 'body';

  // Массив jQuery селекторов к которым будут крепится события (запрос вызывается из атрибута href)
  public $selectors = array();

  public $options = array();
  
  public $ajaxOptions = array();

  public function init()
  {
    parent::init();
    $this->initScripts();
    
    $ajaxOptions = array(
        'success' => new CJavaScriptExpression('function(data){runDialog(data, ' . CJavaScript::encode($this->options['title']) . ')}'),
        'url' => new CJavaScriptExpression('$(this).prop("href")'),
        'cache' => false,
        'error' => new CJavaScriptExpression('function(ans) {console.log(ans.responseText);}'),
    );
    
    $ajaxOptions = CMap::mergeArray($ajaxOptions, $this->ajaxOptions);

    $function = 'function(){jQuery.ajax(' . CJavaScript::encode($ajaxOptions) . ');return false;}';

  if(is_array($this->selectors))
  {
    $js = '';
    foreach($this->selectors as $func => $selector)
      $js .= "$('" . $this->parentSel . "').off('.dialog').on('click.dialog','" . $selector . "', " . $function . ");";

    Yii::app()->getClientScript()->registerScript(__CLASS__.serialize($this->selectors), $js);
  }
  }

  /**
   *  Подкллючает скрипты jui (удобно использовать, если не нужно использовать функционал виджета)
   */
  public function initScripts()
  {
    $this->options = array_merge(
      array(
        "title" => 'Редактирование',
        "hide" => "fade",
        "show" => "fade",
        "modal" => 'true',
        "width"  => "auto",
        "create" => "js: function(event, ui) {
          jQuery(this).css({'max-height': jQuery(window).height()-100, 'max-width': jQuery(window).width()-100, 'overflow-y': 'auto'}); 
  }",
    // "maxHeight"  => 'js:$(document).height()-100',
    "close" => 'js:function(event, ui) {
      $("#"+juiid).remove();
  }', 
  ),
  $this->options
);

  $options=$this->options;
  // титл диалога будет генерироваться самой функцией runDialog, потому этот элемент должен быть JavaScript переменной
  $options['title'] = new CJavaScriptExpression('title');
  $options=CJavaScript::encode($options);

  $js = "var id=0;";
  $js .= 'window.runDialog = function(data, title) {
    var juiid = "juidialog_"+id++;
    $("<div id=\'"+ juiid + "\' style=\"display:none;\" class=\"hDialog\">" + data + "</div>").appendTo("body");
    $("#"+juiid).dialog(' . $options . ');
    return juiid;
  };';
  Yii::app()->getClientScript()->registerScript(__CLASS__, $js);
  }
}
