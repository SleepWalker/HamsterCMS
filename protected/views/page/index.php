<?php
$this->breadcrumbs=array(
	$model->title
);

$this->pageTitle = $model->title;
?>

<div class="view">
<?php
$this->beginWidget('application.widgets.lightbox.HLightBox');

echo $model->content; 

$this->endWidget('application.widgets.lightbox.HLightBox');
?>
</div>

