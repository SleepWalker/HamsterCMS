<?php
/**
 * @var \contest\models\Request[] $requests
 * @var \contest\models\Contest[] $contest
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
    overflow: wrap; /* disable font resizing */
    width:100%;

    page-break-inside:avoid;
    autosize: 1;
}
td {
    font-size: 10pt;
}
td td {
    border:1px solid #000;
    padding: 1mm 0.5mm;
}
.important {
    font-weight: bold;
    width: 25%;
}
</style>


<?php
foreach ($requests as $index => $request) {
?>
<table<?php if ($index) echo ' style="margin-top: 20px;"'; ?>>
    <tr>
        <td>
            <table>
                <tr>
                    <td style="width: 15mm; background:#ddd;"><b><?= $index+1 ?>-<?= $request->id ?></b></td>
                    <td><?= date('d.m.Y', strtotime($request->date_created)) ?></td>
                    <td><?= $request->getFormatLabel() ?></td>
                    <td><?= $request->getAgeCategoryLabel(); ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table>
                <tr>
                    <td>
                        Контакти:
                        <b><?= $request->contact_name ?></b>,
                        <?= $request->contact_email ?>,
                        <?= $request->contact_phone ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <?php if ($request->isGroup()): ?>
    <tr>
        <td>
            <table>
                <tr>
                    <td>
                        <?= $request->getAttributeLabel('name') ?>:
                        "<b><?= $request->name ?></b>"
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <?php endif; ?>
    <tr>
        <td>
    <?php
    foreach ($request->musicians as $index => $musician) {
        $this->renderPartial('_export_musician', [
            'index' => $index,
            'musician' => $musician,
        ]);
    }
    ?>
        </td>
    </tr>
    <tr>
        <td>
            <table>
                <tr>
                    <td width="50%">
                        <b><?= $request->getAttributeLabel('compositions') ?></b>
                    </td>
                    <td style="width: 11mm">
                        <b>хв</b>
                    </td>
                    <td>
                        <b>Демо</b>
                    </td>
                </tr>
    <?php
    foreach ($request->compositions as $index => $composition) {
        $this->renderPartial('_export_composition', [
            'index' => $index,
            'demos' => $request->demos,
            'composition' => $composition,
        ]);
    }
    ?>
            </table>
        </td>
    </tr>
</table>
<?php
}
?>

<?php
require __DIR__ . '/_export_header.php';
?>

<htmlpagefooter name="footer">
{PAGENO}
</htmlpagefooter>
