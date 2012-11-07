<?php
/**
 *  Рендерит Бренды/Категории/Список товаров
 */
if ($title == '') $title = 'Нет результатов';
$this->breadcrumbs=$breadcrumbs;
$this->breadcrumbs[] = $title;

$this->pageTitle = $title;
if($dataProvider->pagination->currentPage)
  $this->pageTitle .= ' - Страница ' . ($dataProvider->pagination->currentPage + 1) . ' из ' . $dataProvider->pagination->pageCount;
?>

<?php 
//$notCached = true; // рендерим CListView
// кешируем действия shop/category и shop/brand, однако только в тех случаях, когда у нас нету фильтра

// кэширование временно (а может и на всегда) отключаем, так как страницы и так шустро грузятсяo
/*
if($itemView != '_view')
  $notCached = $this->beginCache('index' . $_GET['alias'], array(
    'duration'=>3600,
  ));
 */
//if($notCached)
//{ 
if($itemView == '_view')
{
  // фильтр товаров по цене и рейтингу
  echo '<section class="shopSortSection">';
  echo CHtml::beginForm('', 'GET');
  echo CHtml::label('Показывать: ', 'Shop_sort');
  echo CHtml::dropDownList('Shop_sort', $_GET['Shop_sort'], array(
    'rating.desc' => 'Сперва популярные',
    'price' => 'Сперва дешевые',
    'price.desc' => 'Сперва дорогие',
  ), array(
    'onchange' => 'this.form.submit()' 
  ));
  echo CHtml::endForm();
  echo '</section>';
}
$this->widget('zii.widgets.CListView', array(
  'dataProvider'=>$dataProvider,
  'itemView'=>$itemView,
  'summaryText'=>'<h1>' . $title . '</h1>',
  'pager'=>array(
    'cssFile'=>false,
    'header'=>false,
  ),
  'cssFile'=>false,
  'ajaxUpdate' => false,
)); 
  //if($itemView != '_view')
  //  $this->endCache();
//}
?>
