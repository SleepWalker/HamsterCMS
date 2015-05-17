<?php
/**
 * @var string $jury
 * @var string $nomination
 * @var string $ageCategory
 * @var \contest\models\Request[] $requests
 */
?>

<table>
<tr>
<td>

    <table>
        <tr>
            <td>
                Карточка жюри: <b><?= mb_strtoupper($jury, 'utf-8') ?></b>
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
        <tr style="text-rotate: 90;">
            <th rowspan="2" style="text-rotate: 0;">
                Исполнитель
            </th>
            <th rowspan="2" style="text-rotate: 0;">
                Программа
            </th>
            <th rowspan="2" class="note">
                № выступления
            </th>
            <th colspan="2" style="text-rotate: 0;">
                Техника
            </th>
            <th rowspan="2" class="note">
                Артистичность
            </th>
            <th rowspan="2" class="note">
                Соотв. тематике
            </th>
            <th rowspan="2" class="note">
                Сумма оценок
            </th>
            <th rowspan="2" class="note">
                Общая сумма
            </th>
            <th rowspan="2" class="comment" style="text-rotate: 0;">
                Комментарий
            </th>
        </tr>

        <tr>
            <th class="note">Вокал</th>
            <th class="note">Инстр.</th>
        </tr>

        <?php foreach($requests as $request): ?>
            <tr>
                <td rowspan="2"><?= $request->isGroup() ? $request->name : $request->musicians[0]->getFullName() ?></td>
                <td><?= $request->compositions[0]->getFullName() ?></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td rowspan="2"></td>
                <td rowspan="2"></td>
            </tr>

            <tr>
                <td><?= $request->compositions[1]->getFullName() ?></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        <?php endforeach; ?>
    </table>

</td>
</tr>
</table>
