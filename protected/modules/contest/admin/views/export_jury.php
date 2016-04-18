<?php
/**
 * @var array $lists
 * @var array $juries
 */
?>
<style>
@page {
    size: landscape;

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
    font-size: 8pt;
}

.note {
    width: 12mm;
}

.comment {
    width: 80mm;
}
</style>

<?php
set_time_limit(200);

$controller = $this;
$needPageBreak = false;
$renderNominationPartial = function ($view, $data = []) use ($lists, $controller, &$needPageBreak) {

    foreach ($lists as $nomination => $agesList) {
        foreach ($agesList as $ageCategory => $requests) {
            if ($needPageBreak) {
                echo '<pagebreak />';
            }
            $needPageBreak = true;

            $controller->renderPartial($view, array_merge([
                'nomination' => $nomination,
                'ageCategory' => $ageCategory,
                'requests' => $requests,
            ], $data));
        }
    }
}
?>

<?php
foreach ($juries as $jury) {
    $renderNominationPartial('_export_jury', ['jury' => $jury]);
}
?>
<?php $renderNominationPartial('_export_jury_summary', ['juries' => $juries]) ?>

<?php
require __DIR__ . '/_export_header.php';
?>

<htmlpagefooter name="footer">
    <table class="bordered">
        <tr>
            <td class="important hint">Оценка выставляется по 5-ти бальной шкале</td>
        </tr>
    </table>
</htmlpagefooter>
