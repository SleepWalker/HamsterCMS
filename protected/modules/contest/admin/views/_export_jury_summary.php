<?php
/**
 * @var string $nomination
 * @var string $ageCategory
 * @var array $juries
 * @var \contest\models\Request[] $requests
 */
?>

<table>
<tr>
<td>

    <table>
        <tr>
            <td>
                Сводка по номинации
            </td>
            <td>
                <?= $nomination ?>
            </td>
            <td>
                <?= $ageCategory ?>
            </td>
        </tr>
    </table>

</td>
</tr>
<tr>
<td>

    <table>
        <tr>
            <th>
                Исполнитель
            </th>
            <?php foreach ($juries as $jury): ?>
                <th>
                    <?= $jury ?>
                </th>
            <?php endforeach; ?>
            <th style="width: 20mm;">
                МЕСТО
            </th>
        </tr>


        <?php foreach($requests as $request): ?>
            <tr>
                <td><?= $request->isGroup() ? $request->name : $request->musicians[0]->getFullName() ?></td>
                <?= str_repeat('<td></td>', count($juries) + 1) ?>
            </tr>
        <?php endforeach; ?>
    </table>

</td>
</tr>
</table>
