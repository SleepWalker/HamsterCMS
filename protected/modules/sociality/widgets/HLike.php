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
	protected $_assetsUrl;
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

  /**
   * @property string $size размер социальных кнопок (small|medium|standard|big)
   */
  public $size = 'big';

  /**
   * @property string $annotation позиция аннотации с количеством лайков
   */
  public $annotation = 'counter';

	public function init() {
		if (empty($this->_assetsUrl))
			$this->_assetsUrl = Yii::app()->getAssetManager()->publish(
				dirname(__FILE__). DIRECTORY_SEPARATOR.'assets'
			);
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

    if(!empty(Yii::app()->params['vkApiId']))
      $cs->registerMetaTag(Yii::app()->params['vkApiId'], NULL, NULL, array('property' => 'vk:app_id'));

    if($this->size != 'pie')
    {

      $this->registerClientScript();
      switch($this->size)
      {
      case 'big':
        $size['google'] = 'tall';
        $size['vk'] = 'vertical';
        $size['facebook'] = 'box_count';
        $size['twitter'] = array('vertical', 'vertical');
        break;
      case 'standard':
        $size['google'] = 'standard';
        $size['vk'] = 'button';
        $size['facebook'] = 'button_count';
        $size['twitter'] = array('large', '');
        break;
      case 'medium':
        $size['google'] = 'medium';
        $size['vk'] = 'mini';
        $size['facebook'] = 'button_count';
        $size['twitter'] = array('', '');
        break;
      case 'small':
        $size['google'] = 'small';
        $size['vk'] = 'mini';
        $size['facebook'] = 'button_count';
        $size['twitter'] = array('', '');
        break;
      }
?>
  <section class="hlike"<?php echo $this->vertical ? '' : ' style="height:65px"';?>> 
      <a href="https://twitter.com/share" class="twitter-share-button" data-count="<?php echo $size['twitter'][1]; ?>" data-size="<?php echo $size['twitter'][0]; ?>" data-via="">Tweet</a> <?php if($this->vertical) echo '<br />'; ?>
      <fb:like send="false" style="vertical-align: top;margin-top:1px;" layout="<?php echo $size['facebook']; ?>" show_faces="true"></fb:like><?php if($this->vertical) echo '<br />'; ?>
      <div class="g-plusone" data-size="<?php echo $size['google']; ?>"></div>
      <div id="vklike"<?php if(!$this->vertical) echo 'style="display:inline-block;"'; ?>></div> 
    </section>
<?php
      $cs->registerScript(__CLASS__, "$.hvklike({type: '{$size['vk']}', height: 24});", CClientScript::POS_END);
    }
    else
    {
      $cs->registerScriptFile('https://www.google.com/jsapi', CClientScript::POS_END);
      $cs->registerScriptFile($this->_assetsUrl.'/js/'.'socialPie.js', CClientScript::POS_END);
      $this->render('pie', array(
        'title' => $this->title,
      ));
    }
  }

	protected function registerClientScript(){
		$cs = Yii::app()->clientScript;
    $scriptFile = YII_DEBUG ? 'social.js' : 'social.min.js';
		$cs->registerScriptFile($this->_assetsUrl.'/js/'.$scriptFile, CClientScript::POS_END);
	}
}
