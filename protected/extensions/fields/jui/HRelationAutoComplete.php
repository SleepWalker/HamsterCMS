<?php
/**
 * Widget to display jui auto complete field that searches by related model to fill it's id
 *
 * @author Sviatoslav <dev@udf.su>
 * @version 1.0
 * @package JuiAutoDepComplete
 */

Yii::import('zii.widgets.jui.CJuiAutoComplete');

Yii::setPathOfAlias('hrac', dirname(__FILE__));
class HRelationAutoComplete extends CJuiAutoComplete
{
	// TODO: экшены
	public static function actions()
	{
		return array(
			'ac' => 'hrac.actions.HRACAction',
			);
	}

	// временная замена экшена
	public static function executeAction()
	{
		if(!isset($_POST['modelName']) && !isset($_POST['attribute']) && !isset($_POST['searchBy']))
			throw new CHttpException(404, 'Wrong Request');

		$criteria = new CDbCriteria();
		foreach ($_POST['searchBy'] as $attribute)
		{
			$criteria->compare($attribute, $_GET['term'], true, 'OR');
		}

		$models = $_POST['modelName']::model()->findAll($criteria);
		$data = array();
		foreach ($models as $model)
		{
			array_push($data, array(
				'id' => $model->primaryKey,
				'label' => $model->{$_POST['attribute']},
				'value' => $model->{$_POST['attribute']},
				));
		}
		// TODO: сделать "Новая запись" заметной(жирной)
		array_push($data, array(
			'id' => '',
			'label' => 'Новая запись',
			'value' => $_GET['term'],
			));

		header('application/json');
		echo CJSON::encode($data);
	}

	/**
	 * @var string $relation the name of related Model class
	 */
	public $relation;

	/**
	 * @var string $relationAttribute the name of attribute of related Model class
	 */
	public $relationAttribute;

	/**
	 * @var array $searchAttributes the attributes wich be used to search the model. Can be array or comma-separated. Default: $relationAttribute
	 */
	public $searchAttributes;

	public function run()
	{
		if(!$this->hasModel())
			throw new CException('The HRelationAutoComplete::model property is required');
		if(!isset($this->relation))
			throw new CException('The HRelationAutoComplete::relation property is required');
		if(!isset($this->relationAttribute))
			throw new CException('The HRelationAutoComplete::relationAttribute property is required');
		if(!isset($this->searchAttributes))
			$this->searchAttributes = $this->relationAttribute;
		if(!is_array($this->searchAttributes))
			$this->searchAttributes =  preg_split('/ ?, ?/', $this->searchAttributes);

		list($name,$id)=$this->resolveNameID();

		echo CHtml::activeHiddenField($this->model, $this->attribute);

		$this->options['select'] = new CJavaScriptExpression('function (event, ui) {
			$(this).val(ui.item.value);
			$(this).siblings("[type=hidden]").val(ui.item.id);

			return false;
		}');

		$this->options['change'] = new CJavaScriptExpression('function(event,ui){
			if(!ui.item)
			{
				$(this).val("");
				$(this).siblings("[type=hidden]").val("");
			}
		}');

		$relations = $this->model->relations();
		$modelName = $relations[$this->relation][1];

		// now lets swap the field to that that will be used for autocomplete
		$relAtt = $this->attribute;
		CHtml::resolveName($this->model,$relAtt);
		$relAtt = str_replace($relAtt, $this->relationAttribute, $this->attribute);
		$this->name = CHtml::activeName($modelName, $relAtt);
		$this->value = $this->model->{$this->relation} ? $this->model->{$this->relation}->{$this->relationAttribute} : '';
		// TODO: людской URL (нужно провести рефакторинг базового контроллера админки AdminAction -> Controller)
		$this->source = $this->controller->curModuleUrl.'hrac';
		// TODO: здесь может быть уязвимость. вместо modelName можно подставить любой класс
		$this->source = new CJavaScriptExpression('function( request, response ) {
			$.ajax( "'.$this->source.'?term="+request.term, {
				type: "post",
				dataType: "json",
				data: {modelName: '.CJavaScript::encode($modelName).', attribute: '.CJavaScript::encode($this->relationAttribute).', searchBy: '.CJavaScript::encode($this->searchAttributes).'},
				success: response,
			});
		}');
		$this->model = null;
		$this->attribute = null;


		list($name,$id)=$this->resolveNameID();
		if(isset($this->htmlOptions['id']))
			$id=$this->htmlOptions['id'];
		else
			$this->htmlOptions['id']=$id;
		if(isset($this->htmlOptions['name']))
			$name=$this->htmlOptions['name'];

		if(!isset($this->htmlOptions['class']))
			$this->htmlOptions['class'] = '';
		$liveClass = "ac-{$this->id}";
		$this->htmlOptions['class'] .= " $liveClass";

		echo CHtml::textField($name,$this->value,$this->htmlOptions);

		if($this->sourceUrl!==null)
			$this->options['source']=CHtml::normalizeUrl($this->sourceUrl);
		else
			$this->options['source']=$this->source;

		$options=CJavaScript::encode($this->options);
		Yii::app()->getClientScript()->registerScript(__CLASS__.'#'.$id,"jQuery('body').on('focus', '.$liveClass', function(){jQuery(this).autocomplete($options)});");

		// $this->select = CMap::mergeArray(array('value' => $this->attribute), $this->select);
		// $model = $this->model;
		// $attsRelationJs = '';
		// array_walk($this->select, function($v, $k) use(&$attsRelationJs, $model) {$attsRelationJs .= '$("#'.CHtml::activeId($model, $v).'").val(ui.item.'.$k.').prop("readonly", true);'.PHP_EOL;});

		// $attsRelationJs .= PHP_EOL.'$("#'.$id.'Reset").show();';


		// $readonly = !empty($this->model->{$this->attribute});
		// $this->htmlOptions = CMap::mergeArray(array(
		// 	'readonly' => $readonly,
		// 	), $this->htmlOptions);

		// echo '<span style="position: relative; overflow: hidden; display: block;">';
		// parent::run();

		// $attsEmptyJs = '';
		// array_walk($this->select, function($v, $k) use(&$attsEmptyJs, $model) {$attsEmptyJs .= '$("#'.CHtml::activeId($model, $v).'").val("").prop("readonly", false);'.PHP_EOL;});
		// Yii::app()->clientScript
		// 	->registerScript(__CLASS__.'#'.$id.'Reset', '
		// 		$("#'.$id.'Reset").click(function(){
		// 			'.$attsEmptyJs.'
		// 			$(this).hide();
		// 		});
		// 		')
		// 	// что бы не плодить кучу записей в бд будем принудительно подставлять совпавшую строку
		// 	->registerScript(__CLASS__.'#'.$id.'ForceSuggest','
		// 		var curSuggestions = [];
		// 		jQuery("#'.$id.'").on("autocompleteresponse", function(event, ui) {
		// 			if(ui.content.length > 0)
		// 			{
		// 				curSuggestions = ui.content;
		// 			}
		// 		})
		// 		.on("autocompletechange", function(event, ui) {
		// 			if(!ui.item && curSuggestions.length > 0)
		// 			{
		// 				for(var i = 0; i < curSuggestions.length; i++)
		// 				{
		// 					if($.trim(curSuggestions[i].value.toLowerCase()) == $.trim($(this).val().toLowerCase()))
		// 					{
		// 						ui.item = curSuggestions[i];
		// 						'.$attsRelationJs.'

		// 						$(this).parent().popover({
		// 							placement: "left",
		// 							trigger: "manual",
		// 							title: "Поле було автоматично зв\'язано з існуючим записом",
		// 							content: "<button class=\"btn btn-success\" onclick=\"$(\'#"+$(this).prop("id")+"\').parent().popover(\'destroy\');return false;\">Ok</button> <button class=\"btn\" onclick=\"$(\'#'.$id.'Reset\').click();$(this).prev().click();$(\'#"+$(this).prop("id")+"\').val(\'"+$(this).val()+"\');return false;\">Ні, це новий запис</button>",
		// 							html: true,
		// 						}).popover("show");

		// 						break;
		// 					}
		// 				}
		// 			}
		// 		});');


		// echo '<i class="icon icon-remove" style="cursor: pointer; display: '.($readonly?'block':'none').'; position: absolute; z-index: 2; right: 10px; top: 5px;" id="'.$id.'Reset"></i>';
		// echo '</span>';
	}
}
