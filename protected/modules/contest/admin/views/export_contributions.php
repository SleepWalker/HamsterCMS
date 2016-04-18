<?php
/**
 * @var \contest\models\Request[] $requests
 */
?>
<style>
@page {
    margin-top: 27mm;
    margin-bottom: 6mm;
    margin-left: 6mm;
    margin-right: 6mm;

    header: html_header;
    footer: html_footer;
}
table {
    border-collapse:collapse;
   /* overflow: wrap; */ /* disable font resizing */
    width:100%;

    /* page-break-inside:avoid;
    autosize: 1; */
}
td {
    font-size: 12pt;
}
.bordered td,
td td,
td th {
    border: 1px solid #000;
    padding: 1mm 0.5mm;
}

td th,
.important {
    font-weight: bold;
}

.hint,
td th {
    font-size: 10pt;
}

h1 {
    font-size: 14pt;
    text-align: center;
}
</style>

<h1>Список добровільних благодійних внесків для проведення конкурсу</h1>

<table>
    <tr>
        <td>

<table>
    <tr>
        <th>#</th>
        <th>Конкурсант</th>
        <th>Тип</th>
        <th>Школа</th>
        <th style="width: 20mm;">Сума</th>
        <th style="width: 20mm;">Підпис</th>
    </tr>
<?php
foreach ($requests as $index => $request) {
?>
    <tr>
        <td><?= $index+1 ?></td>
        <td><b><?= $request->getMainName() ?></b></td>
        <td><?= $request->isGroup() ? 'Група' : 'Соло' ?></td>
        <td><?= $request->musicians[0]->school ?></td>
        <td></td>
        <td></td>
    </tr>
<?php
}
?>
</table>

        </td>
    </tr>
</table>

<?php
require __DIR__ . '/_export_header.php';
?>

<htmlpagefooter name="footer">
</htmlpagefooter>
