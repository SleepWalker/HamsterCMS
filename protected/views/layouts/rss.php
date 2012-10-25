<?php 
header('Content-Type: text/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL; ?>
<rss version="2.0">    
  <channel>
    <title><?php echo Yii::app()->name . ' - ' . $this->module->params->moduleName . ' - RSS' ?></title>
    <link><?php echo Yii::app()->createAbsoluteUrl('/'); ?></link>
    <description><?php echo Yii::app()->params['RssDescription'] ?></description>
    <copyright><?php echo strip_tags(Yii::app()->params['copyright']) ?></copyright>
    <generator>HamsterCMS</generator>
    <!--language>en-us</language>
    <pubDate>Tue, 10 Jun 2003 04:00:00 GMT</pubDate>
 
    <lastBuildDate>Tue, 10 Jun 2003 09:41:01 GMT</lastBuildDate-->
    <?php echo $content ?>   
  </channel>
</rss>