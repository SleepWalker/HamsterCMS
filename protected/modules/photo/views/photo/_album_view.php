<?php
/* @var $this AlbumController */
/* @var $data Album */
?>

<div class="album">

  <h4><?php echo CHtml::link(CHtml::encode($data->name), array('view', 'id'=>$data->id)); ?></h4>
	
  
  <?php if($data->photo)echo CHtml::image($data->photo->preview_url(), CHtml::encode($data->name)); ?>
	
  
	<p><?php echo CHtml::encode($data->desc); ?></p>

</div>
