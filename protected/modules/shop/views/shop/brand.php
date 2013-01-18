<?php
/**
 * Вьюха выводит описание бренда, а так же категории, 
 * в которых есть товары этого бренда. Соответствует uri: /shop/brand/`brand_alias`
 *
 * @var Brand $brand модель текущего бренда
 * @var array $cats массив категорий для текущего бренда
 */

$this->pageTitle = $brand->brand_name . ' - ' . Yii::app()->name;
?>
<section class="shopBrand">
  <h1><?php echo CHtml::encode($brand->brand_name); ?></h1>
  <article class="hContent">
    <header>
<?php echo CHtml::image(Brand::$uploadsUrl . $brand->brand_logo, CHtml::encode($brand->brand_name), array('width'=>120)); ?>
    </header>
<?php
    echo $brand->description;
    ?>
  </article>
  <h2>Товары производителя <?php echo CHtml::encode($brand->brand_name); ?></h2>
  <section class="hGrid">
  <?php
  foreach($cats as $cat)
  {
    $this->renderPartial('_cat', array(
      'data' => $cat,
    ));
  }
  ?>
  </section>
</section>

