<article class="brand">
  <header>
  <?php
  echo CHtml::link(
    CHtml::image(Brand::$uploadsUrl . $data->brand_logo, CHtml::encode($data->brand_name), array('width'=>120)),
    '/shop/brand/'.$data->brand_alias
  );?>
  </header>
  <footer>
    <h1>
	    <?php 
	      echo CHtml::link(CHtml::encode($data->brand_name), array('/shop/brand/'.$data->brand_alias));
	    ?>
	  </h1>
	</footer>
</article>
