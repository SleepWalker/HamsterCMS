<?php
/* @var $this AlbumController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Разделы галереи',
);

$this->menu=array(
//	array('label'=>'Create Album', 'url'=>array('create')),
//	array('label'=>'Manage Album', 'url'=>array('admin')),
);
?>

<h1>Разделы галереи</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_album_view',
  'itemsCssClass' => 'gridC photoGrid',
  'summaryText' => '',
)); ?>
