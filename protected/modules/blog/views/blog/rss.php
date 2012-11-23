<?php
$posts = $dataProvider->getData();
foreach($posts as $post)
{
?>
<item>
  <title><![CDATA[<?php echo $post->title; ?>]]></title>
  <link><?php echo Yii::app()->createAbsoluteUrl($post->viewUrl); ?></link>
  <description>
  <![CDATA[
  <?php echo CHtml::image(Yii::app()->request->hostInfo.$post->src(), $post->title) . str_replace('{%CUT%}', '', $post->content); ?>
  ]]>
  </description>
  <pubDate><?php echo gmdate('r', strtotime($post->add_date)) ?></pubDate>
  <author><?php echo $post->user->first_name; ?></author>
  <category><?php echo $post->tags; ?></category>
</item>
<?php
}
?>
