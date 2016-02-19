<?php
$this->pageTitle = 'Резервные копии';
?>

<?= CHtml::beginForm() ?>
<p>
    <?= \CHtml::submitButton('Сделать резервную копию') ?>
    <?= \CHtml::submitButton('Очистить базу данных', ['name'=>'flushDb', 'disabled'=>'disabled']) ?>
</p>
<?= CHtml::endForm() ?>

<div class="form">
    <?= CHtml::beginForm('', 'post', [
        'enctype' => 'multipart/form-data',
    ]) ?>
    <div class="row">
        <label for="dump">Upload backup:</label>
        <input required type="file" name="dump" id="dump" />
    </div>

    <div class="row">
        <?= \CHtml::submitButton('Upload') ?>
    </div>
    <?= CHtml::endForm() ?>
</div>

<?php
$this->widget('zii.widgets.grid.CGridView', [
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'name' => 'name',
            'header' => 'Имя файла',
        ],
        [
            'name' => 'formattedSize',
            'header' => 'Размер',
        ],
        [
            'name' => 'time',
            'header' => 'Дата',
            'value' => '\\Yii::app()->dateFormatter->formatDateTime($data["time"])',
        ],
        [
            'class' => 'zii.widgets.grid.CButtonColumn',
            'header' => '',
            'template' => '{download} {restore} {delete}',
            'buttons' => [
                'download' => [
                    'url' => '"?download=" . $data["name"]',
                    'options' => [
                        'title' => 'Скачать бекап',
                        'class' => 'icon_sort_desc',
                    ],
                ],
                'restore' => [
                    'url' => '"?restore=" . $data["name"]',
                    'options' => [
                        'title' => 'Восстановить бекап',
                        'class' => 'icon_refresh',
                    ],
                ],
                'delete' => [
                    'url' => '"?delete=" . $data["name"]',
                    'options' => [
                        'title' => 'Удалить бекап',
                        'class' => 'icon_delete',
                    ],
                ],
            ],
        ],
    ],
    'cssFile' => false,
    'ajaxUpdate' => false,
    'pager' => [
        'cssFile' => false,
        'header' => false,
    ],
]);
