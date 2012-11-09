<?php	
/**
 * TranslitWidget ads transliteration ability for input fields
 * Adds onblur transliteration and also icon, that will
 * transliterate the value of input tag, that stays earlier in HTML tree
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.widgets.translit.TranslitWidget 
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
	class TranslitWidget extends CWidget
	{
	  
	  public $scriptUrl;
	  
	  /**
	  * Id поля, значение которого будет служить источником информации(для кнопки обновить)
	  */
	  public $sourceFieldId;
	  
	  /**
	  * Имя поля, значение которого будет транслитирироваться
	  */
	  public $attribute;
	  
	  /**
	  * Модель, которой принадлежит поле
	  */
	  public $model;

    /**
     *  Режим транслитерации url адреса (слеэши не будут удаляться из текста) 
     */
    public $urlMode = false;
	  
    public function init()
    {
      $this->scriptUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.widgets.translit.assets'));
      $this->registerScriptFile('translit.jquery.min.js');
      
      $sourceFieldId = get_class($this->model) . '_' . $this->attribute;
      
      $js = '$(document).ready(function(){
            $("#' . $sourceFieldId . '").translit({urlMode:' . ($this->urlMode * 1) . '});
      });';
      
      Yii::app()->getClientScript()->registerScript(__CLASS__.'#TranslitWidget' . $sourceFieldId, $js);
    }
    
    protected function registerScriptFile($fileName,$position=CClientScript::POS_END)
    {
      Yii::app()->getClientScript()->registerScriptFile($this->scriptUrl.'/'.$fileName,$position);
    }
  }
