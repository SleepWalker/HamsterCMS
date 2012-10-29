<?php
/* @var $this AlbumController */
/* @var $data Album */
?>

<article class="block">
	
  <?php echo CHtml::link($data->img('thumb'), $data->viewUrl); ?>
  <header><h4><?php echo CHtml::link(CHtml::encode($data->name), $data->viewUrl); ?></h4></header>

</article>
