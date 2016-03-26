<?php
$this->breadcrumbs = [
    $this->module->params->moduleName,
];

$this->pageTitle = $this->module->params->moduleName;
?>

<h1><?= $this->module->params->moduleName ?></h1>

<?php $this->widget('zii.widgets.CListView', [
    'dataProvider' => $dataProvider,
    'itemView' => '_view',
    'summaryText' => '',
    'cssFile' => false,
]); ?>
