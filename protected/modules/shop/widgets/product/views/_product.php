<article class="widget_product">
  <header>
    <a class="widget_product_category" href="<?php echo $data->cat->viewUrl; ?>"><?php echo $data->cat->cat_name; ?></a>
    <a class="widget_product_name" href="<?php echo $data->viewUrl; ?>"><?php echo $data->product_name; ?></a>
    <div class="widget_product_rating">
      <?php $data->ratingWidget($this); ?>
    </div>
  </header>
  <span class="widget_product_price"><?php echo $data->price; ?><span> грн</span></span>
  <a href="<?php echo $data->viewUrl; ?>"><?php echo $data->img(120); ?></a>
  <footer>
    <a href="<?php echo $data->viewUrl; ?>" class="button">Купить</a>
  </footer>
</article>