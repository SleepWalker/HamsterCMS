<?php
/**
 * Admin action class for blog module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.sectionvideo.admin.SectionvideoAdminController
 * @copyright  Copyright &copy; 2013 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

use hamster\modules\sectionvideo\models\Video as Video;
use hamster\modules\sectionvideo\models\VideoMusicians as VideoMusicians;
use hamster\modules\sectionvideo\models\Teacher as Teacher;
use hamster\modules\sectionvideo\models\Musician as Musician;
use hamster\modules\sectionvideo\models\Instrument as Instrument;
Yii::import('application.modules.event.models.Event');
use \Event;
class ContestAdminController extends HAdminController
{
	/**
	 * @return меню для табов
	 */
	public function tabs() {
		return array(
			''  => 'Все видео',
			'update'  => array(
				'name' => 'Редактирование видео',
				'display' => 'whenActive',
			),
			'create'  => array(
				'name' => 'Добавить видео',
				'display' => 'index',
			),
		);
	}

	/**
	 * Создает или редактирует модель
	 */
	public function actionUpdate()
	{
		if ($this->crudid)
			$model = Video::model()->findByPk($this->crudid);
		else
			$model = new Video;

		$musicians = count($model->musicians) > 0 ? $model->musicians : array(new VideoMusicians);

		$modelName = CHtml::modelName($model);
		$vmModelName = CHtml::modelName($musicians[0]);

		// TODO: Ajax валидация related полей
		// AJAX валидация
		if(isset($_POST['ajax']))
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		if(isset($_POST[$modelName]))
		{
			$model->attributes=$_POST[$modelName];

			$transaction = Yii::app()->db->beginTransaction();
			try
			{
				$valid = $model->save();

				VideoMusicians::model()->deleteAllByAttributes(array('video_id' => $model->primaryKey));
				$musicians = array();
				if(isset($_POST['sortOrder'][$vmModelName]))
				{
					$recentlyAddedIds = array(); // id добавленных в этой транзакции внешних связей
					foreach ($_POST['sortOrder'][$vmModelName] as $oid => $postId)
					{
						$data = $_POST[$vmModelName][$postId];

						$musician_id = $data['musician_id'];
						$instrument_id = $data['instrument_id'];
						$teacher_id = $data['teacher_id'];

						if(empty($musician_id))
						{
							$m = new Musician('simple');
							$mData = $_POST[CHtml::modelName($m)][$postId];
							if(!empty($mData['name']))
							{
								$m->name = $mData['name'];
								$valid = $valid && $m->save();
								$musician_id = $m->primaryKey;
								$recentlyAddedIds[CHtml::modelName($m)][$mData['name']] = $m->primaryKey;
							}
						}

						if(empty($instrument_id))
						{
							$m = new Instrument('simple');
							$mData = $_POST[CHtml::modelName($m)][$postId];
							if(!empty($mData['name']))
							{
								if(isset($recentlyAddedIds[CHtml::modelName($m)][$mData['name']]))
								{
									$instrument_id = $recentlyAddedIds[CHtml::modelName($m)][$mData['name']];
								}else{
									$m->name = $mData['name'];
									$valid = $valid && $m->save();
									$instrument_id = $m->primaryKey;
									$recentlyAddedIds[CHtml::modelName($m)][$mData['name']] = $m->primaryKey;
								}
							}
						}

						if(empty($teacher_id))
						{
							$m = new Teacher('simple');
							$mData = $_POST[CHtml::modelName($m)][$postId];
							if(!empty($mData['fullName']))
							{
								if(isset($recentlyAddedIds[CHtml::modelName($m)][$mData['fullName']]))
								{
									$teacher_id = $recentlyAddedIds[CHtml::modelName($m)][$mData['fullName']];
								}else{
									$parts = explode(' ', $mData['fullName']);
									$m->last_name = array_shift($parts);
									if(isset($parts[0]))
										$m->first_name = array_shift($parts);
									if(isset($parts[0]))
										$m->middle_name = implode(' ', $parts);

									$valid = $valid && $m->save();
									$teacher_id = $m->primaryKey;
									$recentlyAddedIds[CHtml::modelName($m)][$mData['fullName']] = $m->primaryKey;
								}
							}
						}

						$vmModel = new VideoMusicians;
						$vmModel->attributes = array(
							'video_id' => $model->primaryKey,
							'musician_id' => $musician_id,
							'instrument_id' => $instrument_id,
							'teacher_id' => $teacher_id,
							'class' => $data['class'],
							'sort_order' => $oid+1,
							);

						$valid = $valid && $vmModel->save();

						array_push($musicians, $vmModel);
					}
				}


				if($valid)
					$transaction->commit();
				else
					$transaction->rollback();
			}catch(Exception $e){
				$transaction->rollback();
				$model->addError('composition_name', $e->getMessage());
				$valid = false;
			}
			if(count($musicians) == 0) $musicians = count($model->musicians) > 0 ? $model->musicians : array(new VideoMusicians);
			if(!$valid)
				$musicians[0]->addError('musician_id', 'Ошибка при обработке данных музыкантов');
		}

		$this->renderForm($model, array(
			'VideoMusicians' => $musicians,
			));
	}

	/**
	 * Перенаправляет обработку запроса на действие Update
	 */
	public function actionCreate()
	{
		$this->actionUpdate();
	}

	/**
	 *  Выводит таблицу всех товаров
	 */
	public function actionIndex()
	{
		$model=new Video('search');
		$model->unsetAttributes();
		if(isset($_GET['Video']))
			$model->attributes=$_GET['Video'];

		$tags = $model->tagModel()->findAll();
		$tagsMenu = array();
		foreach($tags as $tag)
		{
			array_push($tagsMenu, $tag->name);
		}
		$this->aside = CMap::mergeArray($this->aside, array('Теги' => $tagsMenu));

		$this->render('table',array(
			'dataProvider'=> $model->search(),
			'options' => array(
			 'filter'=>$model,
			),
			'columns'=>array(
				array(
						'name'=>'image',
						'value'=>'Chtml::image($data->thumbnail, $data->caption, array("width" => 100))',
						'type'=>'raw',
						'filter'=>'',
				),
				'caption',
				/*
				array(
						'name'=>'cat_id',
						'value' => '$data->cat->name',
						'filter'=> Categorie::model()->catsList,
				),
				array(
						'name'=>'status',
						'type'=>'raw',
						'value' => '$data->statusName',
						'filter'=> Post::getStatusNames(),
				),
				array(
						'name'=>'user_search',
						'value' => '$data->user->first_name',
				),
				// Using CJuiDatePicker for CGridView filter
				// http://www.yiiframework.com/wiki/318/using-cjuidatepicker-for-cgridview-filter/
				// http://www.yiiframework.com/wiki/345/how-to-filter-cgridview-with-from-date-and-to-date-datepicker/
				// http://www.yiiframework.com/forum/index.php/topic/20941-filter-date-range-on-cgridview-toolbar/
				array(
						'name'=>'add_date',
						'type' => 'datetime',
						'filter' => $this->widget('zii.widgets.jui.CJuiDatePicker', array(
							'model'=> $model,
							'attribute'=>'date_add_from',
							'language' => 'ru',
							'defaultOptions' => array(
								'showOn' => 'focus',
								'showOtherMonths' => true,
								'selectOtherMonths' => true,
								'changeMonth' => true,
								'changeYear' => true,
								'showButtonPanel' => true,
								'autoSize' => true,
								'dateFormat' => "yy-mm-dd",
							)
						), true)
						.
						$this->widget('zii.widgets.jui.CJuiDatePicker', array(
							'model'=> $model,
							'attribute'=>'date_add_to',
							'language' => 'ru',
						), true),
				),
				array(
						'name'=>'edit_date',
						'type' => 'datetime',
						'filter' => $this->widget('zii.widgets.jui.CJuiDatePicker', array(
							'model'=> $model,
							'attribute'=>'date_edit_from',
							'language' => 'ru',
						), true)
						.
						$this->widget('zii.widgets.jui.CJuiDatePicker', array(
							'model'=> $model,
							'attribute'=>'date_edit_to',
							'language' => 'ru',
						), true),
				),
				*/
			),
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete()
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$model = Post::model()->findByPk($this->crudid)->delete();
		}
		else
			throw new CHttpException(400,'Не правильный запрос. Пожалуйста не повторяйте этот запрос еще раз.');
	}

	public function actionHrac()
	{
		Yii::import('ext.fields.jui.HRelationAutoComplete');
		HRelationAutoComplete::executeAction();
	}

	/**
	 *  @return array JSON массив с тегами для jQuery UI AutoComplete
	 */
	public function actionActags()
	{
		$tagsArr = Video::model()->suggestTags($_GET['term']);

		header('application/json');
		echo CJSON::encode($tagsArr);
	}

	/**
	 *  @return array JSON массив с тегами для jQuery UI AutoComplete
	 */
	public function actionAcevent()
	{
		$events = Event::model()->findAll(array(
			'condition'=>'name LIKE :keyword',
			'limit'=> 10,
			'params'=>array(
				':keyword'=>'%'.strtr($_GET['term'],array('%'=>'\%', '_'=>'\_', '\\'=>'\\\\')).'%',
			),
		));

		foreach ($events as &$event)
		{
			$event = array(
				'id' => $event->primaryKey,
				'value' => $event->name,
				'label' => $event->name,
				);
		}

		header('application/json');
		echo CJSON::encode($events);
	}
}
?>
