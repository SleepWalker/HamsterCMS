<?php
/* @var $this AlbumController */
/* @var $data Album */
?>

<div class="photo_preview">

  <h4><?php echo CHtml::link(CHtml::encode($data->name), $data->big_url()); ?></h4>
	
  <?php echo CHtml::image($data->preview_url(), CHtml::encode($data->name)); ?>
	<div class="date"><?php echo $data->date; ?></div>
  
	<p><?php echo CHtml::encode($data->desc); ?></p>
  

</div>
