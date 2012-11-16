<?php
/**
 * HComment widget class file
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.widgets.social.HComment
 * @version    1.0
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
/**
 * HLike это враппер для коммент виджетов социальных сетей Facebook, VK, G+ и Twitter, а так же встроенной формы комментариев hamster.
 *
 * Пример использования виджета
 * <pre>
 * $this->beginWidget('application.widgets.social.HComment');
 * </pre>
 */
class HComment extends CWidget 
{
	/**
	* @property string url ассетов
	*/
	public $assetsUrl;
	/**
	* @property string имя js файла lightbox
	*/
	protected $scriptFile='social.min.js';

	public function init() {
		if (empty($this->assetsUrl))
			$this->assetsUrl = Yii::app()->getAssetManager()->publish(
				dirname(__FILE__). DIRECTORY_SEPARATOR.'assets'
			);

		$this->registerClientScript();
		parent::init();
	}

	public function run() 
  {
    $cs = Yii::app()->clientScript;
    $cs->registerMetaTag(Yii::app()->params['vkApiId'], NULL, NULL, array('property' => 'vk:app_id'));
?>
<section id="vkcomments" style="clear:both;"></section>
<?php
  }

	protected function registerClientScript(){
		$cs = Yii::app()->clientScript;
		$cs->registerScriptFile($this->assetsUrl.'/js/'.$this->scriptFile, CClientScript::POS_END);
	}
}
