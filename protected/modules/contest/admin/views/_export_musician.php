<?php
/**
 * @var integer $index
 * @var \contest\models\Musician $musician
 */
?>
<table style="margin-left: 5px;<?php if ($index) echo ' margin-top:10px;'; ?>">
    <tr>
        <td colspan="2">
            <b><?= $musician->fullName ?></b>, <?= date('d.m.Y', strtotime($musician->birthdate)) ?>
            <i><?= trim(implode(', ', [$musician->email, $musician->phone]), ', ') ?></i>
        </td>
    </tr>
    <tr>
        <td class="important"><?= $musician->instrument ?></td>
        <td><b>Шк./Кл./Викл.</b>: <?= $musician->school ?> / <?= $musician->class ?> / <?= $musician->teacher ?></td>
    </tr>
</table>
