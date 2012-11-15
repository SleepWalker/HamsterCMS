<?php
/* @var $this PhotoController */
/* @var $model Photo */

$title = $model->name;
$this->breadcrumbs=array(
	$this->module->params['moduleName']=>array('index'),
	$title,
);

$this->pageTitle = $title;
?>


<section class="left wideC">

<h1><?php echo $title; ?></h1>
<p>
<?php
$this->beginWidget('application.widgets.lightbox.HLightBox');

echo CHtml::link(
  $model->img(), 
  $model->src('full')
);
 
$this->endWidget('application.widgets.lightbox.HLightBox');
?>
</p>
<time style="float:right;"><?php echo Yii::app()->dateFormatter->formatDateTime($model->date, 'medium', null); ?></time>
<p><?php echo CHtml::link($model->album->name, $model->album->viewUrl) ?></p>
<p><?php echo $model->desc ?></p>
</section>
