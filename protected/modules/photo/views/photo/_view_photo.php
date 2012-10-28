<?php
/* @var $this AlbumController */
/* @var $data Album */
?>

<div class="photo_preview">

  <h4><?php echo CHtml::link(CHtml::encode($data->name), $data->viewUrl); ?></h4>
	
  <?php echo CHtml::link($data->img('thumb'), $data->viewUrl); ?>
	<div class="date"><?php echo $data->date; ?></div>
  
	<p><?php echo CHtml::encode($data->desc); ?></p>
  

</div>
