<?php
/* @var $this AlbumController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Albums',
);

$this->menu=array(
//	array('label'=>'Create Album', 'url'=>array('create')),
//	array('label'=>'Manage Album', 'url'=>array('admin')),
);
?>

<h1>Альбомы</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_album_view',
)); ?>

<hr/>
