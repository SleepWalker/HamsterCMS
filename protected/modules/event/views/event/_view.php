<article class="block <?php echo $this->module->id ?>Partialview">
  <header>
    <p><?php echo CHtml::link(CHtml::encode($data->name), $data->viewUrl) ?></p>
  </header>

	<section>
    <?php echo CHtml::link($data->img, $data->viewUrl); ?>
    <p><strong>Где</strong>: <?php echo CHtml::encode($data->where) ?></p>
    <p><strong>Начало</strong>: <?php echo $data->prettyStartDate ?></p>
    <?php if($data->end_date)
    {
      echo '<p><strong>Конец</strong>: ' . $data->prettyEndDate . '</p>';
    }?>
    <p><strong>Как добраться</strong>: <?php echo CHtml::encode($data->location) ?></p>
	</section>
  <footer>
  </footer>
</article>
