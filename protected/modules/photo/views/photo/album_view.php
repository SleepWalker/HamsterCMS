<?php
/* @var $this AlbumController */
/* @var $model Album */

$this->breadcrumbs=array(
	'Albums'=>array('index'),
	$model->name,
);

$this->menu=array(
/*
	array('label'=>'List Album', 'url'=>array('index')),
	array('label'=>'Create Album', 'url'=>array('create')),
	array('label'=>'Update Album', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete Album', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Album', 'url'=>array('admin')),
*/
);
?>

<h1><?php echo  CHtml::encode($model->name); ?></h1>
<p><?php echo  CHtml::encode($model->desc); ?></p>


<div class="album_photos">
<?php 
  foreach($model->photos as $photo)
    echo $this->renderPartial('_view_photo',$photo);
 ?>
 </div>

