<?php
/**
 * HLike widget class file
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.widgets.social.HLike
 * @version    1.0
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
/**
 * HLike это враппер для лайк виджетов социальных сетей Facebook, VK, G+ и Twitter.
 * У виджета есть четыре необязательных параметра.
 *
 * Пример использования виджета
 * <pre>
 * $this->beginWidget('application.widgets.social.HLike', array(
 *   'title'=> 'название страницы', 
 *   'description' => 'описание страницы',
 *   'imgSrc' => 'абсолютный путь к изображению страницы',
 *   'vertical' => true, // Отобразит виджет вертикально. По умолчанию: false
 * ));
 * </pre>
 */
class HLike extends CWidget 
{
	/**
	* @property string url ассетов
	*/
	public $assetsUrl;
	/**
	* @property string имя js файла lightbox
	*/
	protected $scriptFile='social.min.js';
  /**
   * @property string $imageSrc путь к картинке материала
   */
  public $imgSrc;
  /**
   *  @property string $description описание материала 
   */
  public $description;
  /**
   * @property string $title название материала 
   */
  public $title;
  /**
   * @property boolean $vertical включает вертикальную ориентацию виджета 
   */
  public $vertical = false;

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

    // SEO and Sociality meta
    if(isset($this->description))
    {
    $desc = strip_tags(mb_substr($this->description, 0, 200, 'UTF-8'));
    $cs->registerMetaTag($desc, 'description');
    $cs->registerMetaTag($desc, NULL, NULL, array('property' => 'og:description'));
    }

    if(isset($this->title))
      $cs->registerMetaTag($this->title, NULL, NULL, array('property' => 'og:title'));

    //$cs->registerMetaTag('product', NULL, NULL, array('property' => 'og:type'));
    //$cs->registerMetaTag('Ссылка на материал', NULL, NULL, array('property' => 'og:url'));

    $cs->registerMetaTag(Yii::app()->name, NULL, NULL, array('property' => 'og:site_name'));
    if(isset($this->imgSrc))
    {
      $imgSrc = Yii::app()->getRequest()->getHostInfo() . $this->imgSrc;
      $cs->registerMetaTag($imgSrc, NULL, NULL, array('property' => 'og:image'));
      $cs->registerLinkTag('image_src', NULL, $imgSrc);
    }

    $cs->registerMetaTag(Yii::app()->params['vkApiId'], NULL, NULL, array('property' => 'vk:app_id'));
?>
    <section class="hlike"> 
      <a href="https://twitter.com/share" class="twitter-share-button" data-count="vertical" data-via="">Tweet</a> <?php if($this->vertical) echo '<br />'; ?>
      <fb:like send="false" style="vertical-align: top;margin-top:1px;" layout="box_count" show_faces="true"></fb:like><?php if($this->vertical) echo '<br />'; ?>
      <g:plusone size="tall"></g:plusone> 
      <div id="vklike"<?php if(!$this->vertical) echo 'style="display:inline-block;"'; ?>></div> 
    </section>
<?php
  }

	protected function registerClientScript(){
		$cs = Yii::app()->clientScript;
		$cs->registerScriptFile($this->assetsUrl.'/js/'.$this->scriptFile, CClientScript::POS_END);
	}
}
