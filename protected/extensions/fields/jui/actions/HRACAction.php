<?php
/**
 * Action for handling ajax requests and form validation
 * 
 * @author Sviatoslav <dev@udf.su>
 * @version 1.0
 * @package hmForm
 */

class HRACAction extends CAction
{
	public function run()
	{
		if(Yii::app()->request->isAjaxRequest)
			self::actionFetch();
	}

	public static function actionFetch($models = false, $params = array(), $return = false)
	{
		if(Yii::app()->controller->action->id == 'hmForm.fetch')
			$params = $_GET;
			
		$view = $params['view'];
		$modelClassName = $params['model'];

		// some XSS protection
		$view = str_replace('.', '', $view);

		if($models === false)
		{
			$models = new $modelClassName;

			if(isset($_POST[$modelClassName]))
			{
				// model "submit" we need only validate it, because the real save will be in controller
				$models->setAttributes(self::isAssoc($_POST[$modelClassName]) ? $_POST[$modelClassName] : reset($_POST[$modelClassName]), false);
				if(isset($params[$modelClassName]))
					$models->setAttributes(CMap::mergeArray($models->attributes, $params[$modelClassName]), false);

				$models->isNewRecord = !$models->validate();
				CHtml::$errorSummaryCss = 'alert alert-error';
			}
		}

		if(is_numeric($models))
			$models = $modelClassName::model()->findByPk($models);

		if(!is_array($models))
			$models = array($models);

		if($return)
			ob_start();

		foreach ($models as $index => $model) 
		{
			if($model->isNewRecord && isset($params[$modelClassName]))
				$model->setAttributes(CMap::mergeArray($model->attributes, $params[$modelClassName]), false);

			Yii::app()->controller->renderPartial($view, array(
				'model' => $model,
				'index' => isset($params['index']) ? $params['index']++ : $index,
				), false, Yii::app()->request->isAjaxRequest);
		}

		if($return)
			return ob_get_clean();
	}

	protected static function isAssoc($array) {
		return (bool)count(array_filter(array_keys($array), 'is_string'));
	}
}