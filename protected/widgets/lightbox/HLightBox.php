<?php
/**
 * HLightBox widget class file
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.widgets.lightbox.HLightBox
 * @version    1.0
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
/**
 * HLightBox это враппер для {@link http://lokeshdhakar.com/projects/lightbox2/ Lightbox 2} jQuery плагина.
 * Однако в скриптах и стилях lightbox были сделаны изменения, для лучшей интеграции с Yii (исправленны 
 * ссылки на граффику, так что бы они работали через assets). Так же в скрипт lightbox было добавленно пару
 * строчек, которые не дают размеру изображения привышать область видимости браузера.
 *
 * Пример использования виджета
 * <pre>
 * $this->beginWidget('application.widgets.lightbox.LightBox', array
 *   // есть смысл использовать только в том случае, 
 *   // если вам на одной странице нужно несколько галерей
 *   // id используется в rel="lightbox[$id]"
 *   id'=>'myId', 
 *   // jQuery селектор контейнера, в котором нужно использовать lightbox
 *   'container'=>'#container',
 *   // или даже так
 *   'container' => array(
 *     '#container1',
 *     '#container2',
 *     ...
 *   ),
 * ));
 *
 * // виджету можно передать весь контент страницы не разбираясь
 * // так же виджет сам позаботится об атрибутах rel
 * echo $content; 
 *
 * $this->endWidget('application.widgets.lightbox.LightBox');
 * </pre>
 */
class HLightBox extends CWidget {

	/**
	* @property string url ассетов
	*/
	public $assetsUrl;
	/**
	* @property string имя css файла
	*/
	public $cssFile='lightbox.css';
	/**
	* @property string имя js файла lightbox
	*/
	public $scriptFile='lightbox.min.js';
  /**
   * @property string jquery селектор контейнера, в котором нужно подключить lightbox к изображениям
   */
  public $container=false;

	public function init() {
		if (empty($this->assetsUrl))
			$this->assetsUrl = Yii::app()->getAssetManager()->publish(
				dirname(__FILE__). DIRECTORY_SEPARATOR.'lightbox'
			);

		$this->registerClientScript();
		parent::init();
    
    ob_start();
	}

	public function run() 
  {
    $id=$this->getId();

    $content = ob_get_clean();
    $content = preg_replace('/(<a)([^>]+>[^<]*<img)/ui', '$1 rel="lightbox[' . $id . ']"$2', $content);
    echo $content;
    
    // строчечка js, которая подключит lightbox ко всем изображениям в пределах контейнера
    if($this->container)
    {
      if(is_array($this->container))
        $this->container = implode(' a,', $this->container);

      $this->container .= ' a';
      Yii::app()->clientScript->registerScript(__CLASS__ . '_'.$id,"
        $('".$this->container."').each(
          if(/\.(jpg)|(jpeg)|(png)|(gif)$/.test(this.href)
            $(this).attr('rel','lightbox[".$id."]');
        );
      ",CClientScript::POS_END);
    }
	}

	protected function registerClientScript(){
		$cs = Yii::app()->clientScript;
		$cs->registerCoreScript('jquery');
		$cs->registerScriptFile($this->assetsUrl.'/js/'.$this->scriptFile, CClientScript::POS_END);
		$cs->registerCssFile($this->assetsUrl.'/css/'.$this->cssFile);
	}
}
