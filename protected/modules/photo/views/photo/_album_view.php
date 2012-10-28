<?php
/* @var $this AlbumController */
/* @var $data Album */
?>

<div class="album">

  <h4><?php echo CHtml::link(CHtml::encode($data->name), $data->viewUrl); ?></h4>
	
  
  <?php if($data->photo) echo CHtml::link($data->photo->img('thumb'), $data->viewUrl); ?>
	
  
	<p><?php echo strip_tags($data->desc); ?></p>

</div>
