<?php
$this->breadcrumbs=array(
	$this->module->params->moduleName,
);

$this->pageTitle = $this->module->params->moduleName;
?>

<h1><?php echo $this->module->params->moduleName ?></h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
  'summaryText' => '',
)); ?>

<br />
<h2>Облако тегов</h2>
<section id="tagCloud" role="tags" class="tags">
<?php
  $maxTags=20;
  $tags=Tag::model()->findTagWeights($maxTags);
  
  if(count($tags))
    foreach($tags as $tag=>$weight)
    {
      echo CHtml::link(CHtml::encode($tag) . '<span onclick="location.href=\'' . Tag::model()->tagRssViewUrl($tag) . '\'; return false;" title="Подписаться на RSS тега: '.$tag.'">' . CHtml::encode($tag) . '</span>', Tag::model()->tagViewUrl($tag));
    }
  else
    echo 'Нет тегов.';
?>
</section>
