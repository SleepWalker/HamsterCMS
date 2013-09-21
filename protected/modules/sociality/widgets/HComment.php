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
	protected $_assetsUrl;

  /**
   * @property CActiveRecord $model модель, к которой будут крепиться комменты
   *           лучше выбирать главную модель модуля (если их > 1)
   */
  public $model;

  /**
   * @property string $modelId идентификатор модели или любой другой идентификатор,
   * который будет использоваться, что бы отфильтровать нужные комментарии
   */
  public $modelId;
  /**
   * @property string $modelPk идентификатор в бд или любой другой идентификатор,
   * который будет использоваться, что бы отфильтровать нужные комментарии
   */
  public $modelPk;

	public function init() {
		if (empty($this->_assetsUrl))
			$this->_assetsUrl = Yii::app()->getAssetManager()->publish(
				dirname(__FILE__). DIRECTORY_SEPARATOR.'assets'
			);

		$this->registerClientScript();

    Yii::import('application.modules.sociality.models.*');
		parent::init();
	}

	public function run() 
  {
    $cs = Yii::app()->clientScript;
    if(!empty(Yii::app()->params['vkApiId']))
      $cs->registerMetaTag(Yii::app()->params['vkApiId'], NULL, NULL, array('property' => 'vk:app_id'));

    if($this->model)
    {
      $this->modelId = $this->owner->module ? $this->owner->module->id . '.': '';
      $this->modelId .= get_class($this->model);
      $this->modelPk = $this->model->primaryKey;
    }
    $data  = array(
      'Comment' => array(
        'model_id' => strtolower($this->modelId),
        'model_pk' => $this->modelPk,
      ),
    );
    echo '<div id="HCommentsPlaceholder"></div>';
    $js = "
      $.ajax('/sociality', {
        type: 'POST',
        data: " . CJavaScript::encode($data)  . ",
        success: function(data) {
          $('#HCommentsPlaceholder').replaceWith(data);
        },
      });  
    ";
    $cs->registerScript(__CLASS__, $js, CClientScript::POS_END);
  }

  protected function registerClientScript()
  {
		$cs = Yii::app()->clientScript;
    $scriptFile = YII_DEBUG ? 'social.js' : 'social.min.js';
		$cs->registerScriptFile($this->_assetsUrl.'/js/'.$scriptFile, CClientScript::POS_END);
		$cs->registerScriptFile($this->_assetsUrl.'/js/jquery.autosize-min.js', CClientScript::POS_END);
	}
}
