<?php
/**
* Эта вьюха служит как для отображения фоток в альбоме, так и всех фоток со всех альбомов.
* Если нужно отображение фотока альбома вьюха принимает переменную Album $model 
* Для отображения всех фоток достаточно массива моделей Photo переданного в переменной $photos
* @param array Photo $photos
* @param Album $model
 */
$title = isset($model) ? $model->name : $this->module->params['moduleName'];
if(isset($model))
{
$this->breadcrumbs=array(
	$this->module->params['moduleName']=>array('index'),
	$title,
);
}else{
  $this->breadcrumbs=array(
    $this->module->params['moduleName'],
  );
}

$this->pageTitle = $title;

?>
  <h1><?php echo  CHtml::encode($title); ?></h1>
<?php
if(isset($model))
{
  ?>
  <p><?php echo  $model->desc; ?></p>
  <?php
}
?>
<section class="left wideC gridC photoGrid">
<?php 
if(!$photos) $photos = $model->photos;
if(is_array($photos))
  foreach($photos as $photo)
    $this->renderPartial('_view_photo', array('data'=>$photo));
?>
</section>
