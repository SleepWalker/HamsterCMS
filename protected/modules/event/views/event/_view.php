<?
global $past;
if(strtotime($data->start_date) < time() && !$past)
{
  if($index == 0)
    echo '<p class="noData">Нет мероприятий</p>';

  echo '<h2>Прошедшие мероприятия</h2>';
  $past = true;
}
?>
<article class="partialView <?php echo $this->module->id ?>PartialView">
  <header>
    <h2><?php echo CHtml::link(CHtml::encode($data->name), $data->viewUrl) ?></h2>
    <?php echo CHtml::link($data->mapImage, $data->viewUrl); ?>
  </header>

	<div class="content <?php echo $this->module->id ?>Content">
    <p><strong>Где</strong>: <?php echo CHtml::encode($data->where) ?></p>
    <p><strong>Начало</strong>: <?php echo $data->prettyStartDate ?></p>
    <?php if((int)$data->end_date)
    {
      echo '<p><strong>Конец</strong>: ' . $data->prettyEndDate . '</p>';
    }?>
    <?php if(!empty($data->location)): ?>
      <p><strong>Как добраться</strong>: <?php echo CHtml::encode($data->location) ?></p>
    <?php endif; ?>
	</section>
  <footer>
  </footer>
</article>
