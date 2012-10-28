<?php
/* @var $this PhotoController */
/* @var $model Photo */

$this->breadcrumbs=array(
	'Photos'=>array('index'),
	$model->name,
);

$this->pageTitle = $model->name;

/*$this->menu=array(
	array('label'=>'List Photo', 'url'=>array('index')),
	array('label'=>'Create Photo', 'url'=>array('create')),
	array('label'=>'Update Photo', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete Photo', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Photo', 'url'=>array('admin')),
);*/
?>

<h1><?php echo $model->name; ?></h1>

<p><?php echo $model->img(); ?></p>
<p><?php echo CHtml::link($model->album->name, $model->album->viewUrl) ?></p>
<p><?php echo $model->date ?></p>
<p><?php echo $model->desc ?></p>
