<?php	
/**
 * Extends CJuiWidget for using it with ajax requests
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.ShopController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
  Yii::import('zii.widgets.jui.CJuiWidget');
  
  class AjaxDialogWidget extends CJuiWidget
  {
      // селектор елемента родителя в котором нужно будет искать по селекторам $selectors      
      public $parentSel = 'body';
      
      // Массив jQuery селекторов к которым будут крепится события (запрос вызывается из атрибута href)
      public $selectors;
      
      public $options = array();
      
      public function init()
      {
        if(empty($this->selectors)) return;
        $this->initScripts();

        $function = 'function(){jQuery.ajax({
          "success":runDialog,
          "url":$(this).prop("href"),
          "cache":false,
          "error":function(ans) {
            console.log(ans.responseText);
          },
        });return false;}';
        
        foreach($this->selectors as $func => $selector)
          $js .= "$('" . $this->parentSel . "').on('click','" . $selector . "', " . $function . ");";
        Yii::app()->getClientScript()->registerScript(__CLASS__.$this->parentSel, $js);
      }
      
      /**
       *  Подкллючает скрипты jui (удобно использовать, если не нужно использовать функционал виджета)
       */
      public function initScripts()
      {
        parent::init();
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
        
        $options=CJavaScript::encode($this->options);
        
        $js = "var id=0;";
        $js .= 'window.runDialog = function(data) {
          var juiid = "juidialog_"+id++;
          $("<div id=\'"+ juiid + "\' style=\"display:none;\">" + data + "</div>").appendTo("body");
          $("#"+juiid).dialog(' . $options . ');
          return juiid;
        };';
        Yii::app()->getClientScript()->registerScript(__CLASS__, $js);
      }
  }
