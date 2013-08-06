<?php
$this->breadcrumbs=array(
	$this->module->params->moduleName=>array('index'),
	$model->title,
);

$this->pageTitle = $model->title;
?>

<article class="blogPost">
  <header>
    <h1><?php echo $model->title; ?></h1>
    <?php echo $model->img() ?>
  </header>

	<section class="postContent">
    <?php 
$this->beginWidget('application.widgets.lightbox.HLightBox');
$this->beginWidget('CMarkdown', array('purifyOutput'=>true));
echo str_replace('{%CUT%}', '', $model->content); 
$this->endWidget('CMarkdown');
$this->endWidget('application.widgets.lightbox.HLightBox');
?>
	</section>
  <footer>
<?php
$parts = explode('{%CUT%}', $model->content);
$this->widget('application.modules.sociality.widgets.HLike', array(
  'imgSrc' => $model->src('thumb'),
  'description' => array_shift($parts),
  'title' => $model->title,
));
?>
    <section role="tags" class="tags" style="float:right;">
      <?php
        foreach($model->tagsArr as $tag)
        {
          echo CHtml::link($tag . '<span onclick="location.href=\'' . Tag::model()->tagRssViewUrl($tag) . '\'; return false;" title="Подписаться на RSS тега: '.$tag.'">' . $tag . '</span>', Tag::model()->tagViewUrl($tag));
        }
      ?>
    </section>
  </footer>
</article>
<?php
$this->widget('application.modules.sociality.widgets.HComment', array(
  'model' => $model,
));
?>
