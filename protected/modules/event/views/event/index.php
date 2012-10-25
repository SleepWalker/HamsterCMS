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
