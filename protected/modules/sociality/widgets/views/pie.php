<?php
 $url = Yii::app()->request->hostInfo . Yii::app()->request->url;
?>
<div id="socialPieContainer">
  <div id="socialPie"></div>
  <div id="socialPieTotal">0</div>
  <a href="http://vkontakte.ru/share.php?url=<?php echo $url;  ?>" class="vkButton" target="_blank">Поделиться вКонтакте</a>
  <a href="http://www.facebook.com/sharer.php?u=<?php echo $url;  ?>&t=<?php echo $title; ?>" class="fbButton" target="_blank">Поделиться в facebook</a>
  <a href="http://twitter.com/share?url=<?php echo $url;  ?>&text=<?php echo $title; ?>" class="twButton" target="_blank">Поделиться в twitter</a>
  <a href="https://plus.google.com/share?url=<?php echo $url;  ?>" class="gpButton" target="_blank">Поделиться в Google+</a>
</div>
