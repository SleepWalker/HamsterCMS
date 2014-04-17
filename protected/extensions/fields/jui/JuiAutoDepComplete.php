<?php
/**
 * Widget to display Has Many form
 * 
 * @author Sviatoslav <dev@udf.su>
 * @version 1.0
 * @package JuiAutoDepComplete
 */

Yii::import('zii.widgets.jui.CJuiAutoComplete');

class JuiAutoDepComplete extends CJuiAutoComplete
{
	public $select = array();

	public $iconOptions = array();

	public function run()
	{
		list($name,$id)=$this->resolveNameID();

		if(is_string($this->source))
			$this->source = new CJavaScriptExpression('function( request, response ) {
				$.ajax( "'.Yii::app()->createUrl($this->source).'?term="+request.term, {
					type: "post",
					dataType: "json",
					data: $("#'.$id.'").parents("form").serialize(),
					success: response,
				});
			}');

		$this->select = CMap::mergeArray(array('value' => $this->attribute), $this->select);
		$model = $this->model;
		$attsRelationJs = '';
		array_walk($this->select, function($v, $k) use(&$attsRelationJs, $model) {$attsRelationJs .= '$("#'.CHtml::activeId($model, $v).'").val(ui.item.'.$k.').prop("readonly", true);'.PHP_EOL;});

		$attsRelationJs .= PHP_EOL.'$("#'.$id.'Reset").show();';

		$this->options['select'] = new CJavaScriptExpression('function (event, ui) {
			'.$attsRelationJs.'

			return false;
		}');

		$readonly = !empty($this->model->{$this->attribute});
		$this->htmlOptions = CMap::mergeArray(array(
			'readonly' => $readonly,
			), $this->htmlOptions);

		echo '<div><span style="position: relative; overflow: hidden; display: inline-block;">';
		parent::run();

		$attsEmptyJs = '';
		array_walk($this->select, function($v, $k) use(&$attsEmptyJs, $model) {$attsEmptyJs .= '$("#'.CHtml::activeId($model, $v).'").val("").prop("readonly", false);'.PHP_EOL;});
		Yii::app()->clientScript
			->registerScript(__CLASS__.'#'.$id.'Reset', '
				$("#'.$id.'Reset").click(function(){
					'.$attsEmptyJs.'
					$(this).hide();
				});
				')
			// что бы не плодить кучу записей в бд будем принудительно подставлять совпавшую строку
			->registerScript(__CLASS__.'#'.$id.'ForceSuggest','
				var curSuggestions = [];
				jQuery("#'.$id.'").on("autocompleteresponse", function(event, ui) {
					if(ui.content.length > 0)
					{
						curSuggestions = ui.content;
					}
				})
				.on("autocompletechange", function(event, ui) {
					if(!ui.item && curSuggestions.length > 0)
					{
						for(var i = 0; i < curSuggestions.length; i++)
						{
							if($.trim(curSuggestions[i].value.toLowerCase()) == $.trim($(this).val().toLowerCase()))
							{
								ui.item = curSuggestions[i];
								'.$attsRelationJs.'

								$(this).parent().popover({
									placement: "left",
									trigger: "manual",
									title: "Поле було автоматично зв\'язано з існуючим записом",
									content: "<button class=\"btn btn-success\" onclick=\"$(\'#"+$(this).prop("id")+"\').parent().popover(\'destroy\');return false;\">Ok</button> <button class=\"btn\" onclick=\"$(\'#'.$id.'Reset\').click();$(this).prev().click();$(\'#"+$(this).prop("id")+"\').val(\'"+$(this).val()+"\');return false;\">Ні, це новий запис</button>",
									html: true,
								}).popover("show");

								break;
							}
						}
					}
				});');


		if(isset($this->iconOptions['class']))
			$this->iconOptions['class'] .= ' icon icon-remove';

		echo CHtml::tag('i', CMap::mergeArray(array(
			'class' => 'icon icon-remove',
			'style' => 'cursor: pointer; display: '.($readonly?'block':'none').'; position: absolute; z-index: 2; right: 10px; top: 5px;',
			'id' => $id.'Reset',
			),$this->iconOptions), '');
		echo '</span></div>';
	}
}