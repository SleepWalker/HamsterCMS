<article class="block informer event">
  <header>Ближайшее мероприятие <?php echo CHtml::link(CHtml::encode($data->name), $data->viewUrl) ?></header>
    
    <p><?php echo strip_tags($data->desc); ?></p>
  <footer>
  <details><span>Где:</span> <?php echo $data->where ?>
<a href="<?php echo $data->gCalUrl ?>" class="icon icon_gcal" title="Добавить в Google Calendar">Добавить в Google Calendar</a><a href="<?php echo Yii::app()->createUrl('event/event/ical', array($data->eventId));  ?>" class="icon icon_ical" title="iCalendar (*.ics)">iCalendar (*.ics)</a>

<time timestamp=""><span>Начало:</span> <?php echo $data->prettyStartDate ?></time></details>
  </footer>
</article>
<?php
//<a href="" class="icon icon_ymaps" title="Посмотреть на Яндекс Картах">Посмотреть на Яндекс Картах</a>
?>
