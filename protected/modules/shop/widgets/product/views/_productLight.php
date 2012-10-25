<article class="widget_productLight">
  <header>
  <a class="widget_productLight_name" href="<?php echo $data->viewUrl; ?>"><?php echo $data->product_name; ?></a>
  </header>
  
  <a href="<?php echo $data->viewUrl; ?>"><?php echo $data->img(120); ?></a>
  <footer>
    <div class="widget_productLight_rating">
      <?php $data->ratingWidget($this); ?>
    </div>
    <div class="widget_productLight_price"><?php echo $data->price; ?><span> грн</span></div>
  </footer>
</article>