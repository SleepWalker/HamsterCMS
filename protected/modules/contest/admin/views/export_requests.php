<?php
/**
 * @var \contest\models\Request[] $requests
 */
?>
<style>
@page {
    margin-top: 30mm;
    margin-bottom: 6mm;

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
    padding: 2px 5px;
}
.instrument {
    font-weight: bold;
    width: 25%;
}
</style>

<htmlpageheader name="header">
    <table>
        <tr>
            <td>
                <img style="width: 40%; margin-bottom: 20px;" src="data:image/png;base64,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">
            </td>
            <td style="text-align: right">Всеукраинский конкурс «Рок єднає нас» 2015</td>
        </tr>
    </table>
</htmlpageheader>
<htmlpagefooter name="footer">
{PAGENO}
</htmlpagefooter>


<?php
if (!function_exists('median')) {
    function median($arr)
    {
        sort($arr);
        $count = count($arr); //total numbers in array
        $middleval = floor(($count-1)/2); // find the middle value, or the lowest middle value
        if ($count % 2) { // odd number, middle is the median
            $median = $arr[$middleval];
        } else { // even number, calculate avg of 2 medians
            $low = $arr[$middleval];
            $high = $arr[$middleval+1];
            $median = (($low+$high)/2);
        }
        return $median;
    }
}
if (!function_exists('average')) {
    function average($arr)
    {
        $total = 0;
        $count = count($arr); //total numbers in array
        foreach ($arr as $value) {
            $total +=  + $value; // total value of array numbers
        }
        $average = ($total/$count); // get average value
        return $average;
    }
}

foreach ($requests as $index => $request) {
    $isGroup = $request->type == \contest\models\view\Request::TYPE_GROUP;
    $hasVocal = count(array_filter($request->musicians, function ($musician) {
        return preg_match('/вокал/iu', $musician->instrument);
    })) > 0;

    if ($isGroup) {
        $format = 'Группа';
    } else {
        switch ($request->format) {
            case \contest\models\view\Request::FORMAT_SOLO:
                $format = 'Соло';
                break;
            case \contest\models\view\Request::FORMAT_MINUS:
                $format = 'Минус';
                break;
            case \contest\models\view\Request::FORMAT_CONCERTMASTER:
                $format = 'Концертмейстер';
                break;
        }
    }

    switch ((int)$isGroup.(int)$hasVocal) {
        case '00':
            $nomination = 'Инструментальное соло';
            break;
        case '10':
            $nomination = 'Инстр. ансамбль';
            break;
        case '01':
            $nomination = 'Вокальное соло';
            break;
        case '11':
            $nomination = 'Вокально-инстр. ансамбль';
            break;
    }

    $ageMap = [
        10 => 'до 10 лет',
        14 => '11-14 лет',
        17 => '15-17 лет',
        100 => '18 лет и больше',
    ];
    $ageMap = array_reverse($ageMap, true);

    if ($isGroup) {
        $ages = array_map(function ($musician) {
            return $musician->age;
        }, $request->musicians);
        $age = min(median($ages), average($ages));
    } else {
        $age = $request->musicians[0]->age;
    }

    foreach ($ageMap as $ageThreshold => $label) {
        if ($age <= $ageThreshold) {
            $ageCategory = $label;
        }
    }

?>
<table<?php if ($index) echo ' style="margin-top: 20px;"'; ?>>
    <tr>
        <td>
            <table>
                <tr>
                    <td style="width: 10mm; background:#ddd;"><b>#<?= $request->id ?></b></td>
                    <td style="width: 10mm;"></td>
                    <td><?= date('m.d.Y', strtotime($request->date_created)) ?></td>
                    <td><?= $nomination ?></td>
                    <td><?= $ageCategory ?></td>
                    <td><?= $format ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <?php if ($isGroup): ?>
    <tr>
        <td>
            <table>
                <tr>
                    <td>Название коллектива: "<b><?= $request->name ?></b>"</td>
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
                        <b>Композиции</b>
                    </td>
                    <td style="width: 11mm">
                        <b>мин</b>
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
