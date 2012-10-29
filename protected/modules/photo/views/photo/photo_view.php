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

<p><?php echo $model->img(); ?></p>
<p style="float:right;"><?php echo $model->date ?></p>
<p><?php echo CHtml::link($model->album->name, $model->album->viewUrl) ?></p>
<p><?php echo $model->desc ?></p>
</section>
