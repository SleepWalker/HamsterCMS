Характеристики товара:
<?php
if(!count($charShemas))
  echo 'У этой категории нету характеристик';
else
{
?>
<table class="items" id="charGrid">
  <tbody>
    <?php 
      foreach($charShemas as $charShema)
      {
        if($charShema->hasChilds)
        {
          foreach($charShema->ddMenuArr['relatedArr'] as $itemId => $relItem) 
            foreach($relItem as $relId)
            {
              // Добавляем id детей для определенного пункта поля характеристик
              // далее в зависимости от этих данных будут генерироваться id для строк в таблице характеристик
              $parentIdOf[$itemId][$relId] = $charShema->char_id;
            }
        }
      }
      foreach($charShemas as $charShema)
      {
        if($charShema->isCaption && !$charShema->hasSuffix && $charShema->type == 1) continue; // у таких характеристик название выводится как заголовок, потому пропускаем их при заполнении
        echo '<tr class="char' . $charShema->char_id . '"><td>' . $charShema->gridColName() . '</td>
        <td>' . $charShema->gridColValue($charValues[$charShema->char_id]) . '</td></tr>';
      }
    ?>
  </tbody>
</table>
<?php
}
?>