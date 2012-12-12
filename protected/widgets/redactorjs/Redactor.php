<?php
/**
 * Redactorjs widget
 *
 * @author Vincent Gabriel
 * v 1.0
 */
class Redactor extends CInputWidget {
	/**
	 * Editor language
	 * Supports: de, en, fr, lv, pl, pt_br, ru, ua
	 */
	public $lang = 'en';
	/**
	 * Html options that will be assigned to the text area
	 */
	public $htmlOptions = array();
	/**
	 * Editor options that will be passed to the editor
	 */
	public $editorOptions = array();
	/**
	 * Debug mode
	 * Used to publish full js file instead of min version
	 */
	public $debugMode = false;
  
	/**
	 * Display editor
	 */
    public function run() {
	
		// Resolve name and id
		list($name, $id) = $this->resolveNameID();

		// Get assets dir
        $baseDir = dirname(__FILE__);
        $assets = Yii::app()->getAssetManager()->publish($baseDir.DIRECTORY_SEPARATOR.'assets');

		// Publish required assets
		$cs = Yii::app()->getClientScript();
    
        
    // добавляем плагины
    // TODO: дать возможность юзеру добавлять плагины через настройки виджета + добавлять скрипты циклом
    //$cs->registerScriptFile($assets.'/plugins/fullscreen.js');
		
    if($this->lang != 'en')
      $cs->registerScriptFile($assets.'/langs/' . $this->lang . '.js');
		$jsFile = YII_DEBUG ? 'redactor.js' : 'redactor.min.js';
		$cs->registerScriptFile($assets.'/' . $jsFile);
		$cs->registerCssFile($assets.'/css/redactor.css');

    $this->htmlOptions['id'] = $id;

    $options = CJSON::encode(array_merge($this->editorOptions, array('lang' => $this->lang/*, 'plugins' => array('fullscreen')*/, )));

		        $js =<<<EOP
		$('#{$id}').redactor({$options});
EOP;
		// Register js code
		$cs->registerScript('Yii.'.get_class($this).'#'.$id, $js, CClientScript::POS_READY);
	
		// Do we have a model
		if($this->hasModel()) {
            $html = CHtml::activeTextArea($this->model, $this->attribute, $this->htmlOptions);
        } else {
            $html = CHtml::textArea($name, $this->value, $this->htmlOptions);
        }

		echo $html;
    }
}
?>
