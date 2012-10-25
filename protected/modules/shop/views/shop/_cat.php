<article class="categorie">
  <header>
  <?php
  echo CHtml::link(
    CHtml::image(Categorie::$uploadsUrl . $data->cat_logo, CHtml::encode($data->cat_name), array('width'=>120)),
    '/shop/categorie/'.$data->cat_alias
  );?>
  </header>
  <footer>
    <h1>
	    <?php 
	      echo CHtml::link(CHtml::encode($data->cat_name), array('/shop/categorie/'.$data->cat_alias));
	    ?>
	  </h1>
	</footer>
</article>
