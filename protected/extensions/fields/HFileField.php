<?php

/**
 * HFileField виджет для облегчения загрузки и обработки файлов через ajax
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.extensions.fields.HFileField
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class HFileField extends CInputWidget
{
  /**
   * @var string $assetsUrl url папки со скриптами, стилями и графикой виджета
   */
  public $assetsUrl;

	public function init()
	{
    $this->registerClienScript();
	}

	public function run()
	{
    if($this->hasModel())
    {
      $model = $this->model;
      $attribute = $this->attribute;
      if (!empty($model[$attribute]) && !is_array($model[$attribute]))
      {// Выводим картинку (только в случае если картинка одна, тоесть атрибут модели не содержит массив)
        $this->htmlOptions['style'] = 'display:none;';
        echo '<div>';
        echo CHtml::image($model->uploadsUrl . $model[$attribute], $attribute, array('id'=>$attribute.'_tag'));
        echo CHtml::link(CHtml::image($this->controller->adminAssetsUrl.'/images/icon_delete.png','Удалить картинку'), '#', array('id'=>'renewImage'));
        echo '</div>';
        Yii::app()->getClientScript()
        ->registerScript('renewImage','jQuery("#renewImage").bind("click", function() {
          $(this).remove();
          $("#' . $attribute.'_tag").remove();
          $("#yt' . get_class($model).'_'.$attribute . '").val("delete");
          with($("#' . get_class($model).'_'.$attribute . '")) 
          {
            css({display:"block"});
            after("Изображение окончательно удалится/изменится после отправки формы");
          }
          return false;
        });', CClientScript::POS_END);
      }

      echo CHtml::activeFileField($this->model,$this->attribute,$this->htmlOptions);
   }
   else
     echo CHtml::fileField($this->name,$this->value,$this->htmlOptions);
}

	public function registerClienScript()
	{
    $this->assetsUrl = Yii::app()->getAssetManager()->publish(dirname(__FILE__).'/assets',false,-1,YII_DEBUG);
    $cs = Yii::app()->clientScript;
    $cs->registerCoreScript('jquery');
    $cs->registerScriptFile($this->assetsUrl . '/js/fileUploader.js', CClientScript::POS_END); 
	}
}