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
$notCached = true; // рендерим CListView
// кешируем действия shop/category и shop/brand, однако только в тех случаях, когда у нас нету фильтра
if($itemView != '_view')
  $notCached = $this->beginCache('index' . $_GET['alias'], array(
    'duration'=>3600,
  ));
if($notCached)
{ 
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
  if($itemView != '_view')
    $this->endCache();
}
?>
