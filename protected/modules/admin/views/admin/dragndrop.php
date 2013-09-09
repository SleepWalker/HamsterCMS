<?php 
/**
 * View file for generating drag'n'drop model representation
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    admin.views.dragndrop
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
foreach ($models as $model) {
  // Добавлем элемент в общий список
  $tree[ $model[$attId] ][0] = '<li sindex="' . $model[ $attSindex ] . '" id="row_' . $model[ $attId ] . '">';
  
  foreach($attributes as $k => $att)
    $tree[ $model[$attId] ][0] .= $model[$att] . (($k)?' - ':'') . renderIcons($this->actionPath, $model[$attId], $model);
  $tree[ $model[$attId] ][0] .= '</li>';
  
  if($model[$attParent] == 0)
  {
    // Добавляем массив родителя в дерево категорий
    $tree[0][] = &$tree[ $model[$attId] ];  
  }
  else
  {
    // Добавляем дитя в массив родителя
    if( !is_array($tree[ $model[$attParent] ]) )
      $tree[ $model[$attParent] ][0] = ''; // так как массива нету, значит мы еще не добрались до родителя. Создадим для него пустой элемент.
    $tree[ $model[$attParent] ][] = &$tree[ $model[$attId] ];
  }
}

if(isset($tree))
{
  $tree = array_values($tree[0]); // реиндексируем массив

  echo '<ul id="dnd">';
  catTreeParse($tree);
  echo '</ul>';
}else{
  echo '<p><i>Нет категорий</i></p>';
}

echo '<p>' . CHtml::link( CHtml::button('Добавить категорию') , $this->actionPath.'create', array ('id'=>'addButton')) . '</p>';
  
/***********************
* #catTreeParse - строит дерево из массива
***********************/
function catTreeParse($tree, $level = -1) {
  for ($i = 0; $i < count($tree); $i++) {
    $item = $tree[$i];
    if (!is_array($item)) // Если не массив - значит это категория, принтим ее html код
      echo $item;
    else
    { // У этой категорий есть дети, парсим ее массив
      if ($level >= 0 && $i == 1) echo '<ul level=' . ($level+1) . '>'; // Очень умное условие для вставки открывающих тегов многоуровневых списков
      catTreeParse($item, $level+1);
    }
  }
  if ($level >= 0 && $i > 1) echo '</ul>'; // Очень умное условие для вставки закрывающих тегов многоуровневых списков
}

/***********************
* Выводит иконки действий
***********************/
function renderIcons($urlPrefix, $id, $model) {
  return CHtml::ajaxLink('', $urlPrefix.'delete/'.$id, array(
    'beforeSend' => new CJavaScriptExpression('function() {return confirm("Вы действительно хотите удалить категорию?")}'),
    'complete' => new CJavaScriptExpression('function() {location.reload()}'),
  ), array('class'=>'icon_delete', 'id'=>'delete'.$id, 'type'=>'post'))
        .CHtml::link('', $urlPrefix.'charshema/'.$id, array('class'=>'icon_table', 'id'=>'table'.$id))
        .CHtml::link('', $urlPrefix.'update/'.$id, array('class'=>'icon_edit', 'id'=>'update'.$id))
        .CHtml::link('', $urlPrefix.'create/'.$id, array('class'=>'icon_add', 'id'=>'create'.$id))
        .CHtml::link('', $model->viewUrl, array('class'=>'icon_view', 'target'=>'_blank'))
  ;
}
  
$this->widget('application.widgets.juiajaxdialog.AjaxDialogWidget', array(
  'id'=>'dnd',
  'selectors' => array(
    '#dnd a.icon_edit',
    '#dnd a.icon_add',
    //'#dnd a.icon_table',
    '#addButton',
  ),
  'themeUrl' => $this->adminAssetsUrl . '/css/jui',
));
?>
