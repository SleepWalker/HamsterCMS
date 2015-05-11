<?php
/**
 * @var integer $index
 * @var string $demos
 * @var \contest\models\Composition $composition
 */
?>
<tr>
    <td><?= $composition->author ?> — <?= $composition->title ?></td>
    <td><?= $composition->duration ?></td>
    <?php
    if (!$index) {
        echo CHtml::tag('td', [
            'rowspan' => 2,
        ], $demos);
    }
    ?>
</tr>
