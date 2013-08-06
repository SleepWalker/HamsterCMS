<?php
/**
 * HMarkitupWidget adds {@link http://markitup.jaysalvat.com/ markitup} as a form field widget.
 *
 * В этой версии добавленная кнопка для загрузки изображений на сервер.
 *
 * @author Sviatoslav Danylenko <dev@udf.su>
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @version 1.1
 * @link http://code.google.com/p/yiiext/
 * @link http://markitup.jaysalvat.com/
 *
 * @depends AjaxDialogWidget
 */
 
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'EMarkitupWidget.php');

class HMarkitupWidget extends EMarkitupWidget
{
    
    public $settings = 'hmarkdown';
    
	/**
	 * Init widget.
	 */
	public function init()
	{
		parent::init();
		
		Yii::app()->controller->widget('ext.jui.AjaxDialogWidget', array(
		    'selectors' => array("#markItUp{$this->id} li.hmdImageUpload"),
		    'options' => array(
		        'title' => 'Загрузка файлов',
		    ),
		    'ajaxOptions' => array(
		        'url' => Yii::app()->createUrl('admin/upload/image'),
		    ),
		));
	}
}
