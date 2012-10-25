<article class="product">
  <header>
  <?php
  $src = $data->photo[0];
  echo CHtml::link(
    $data->img(120),
    '/shop/'.$data->page_alias
  );?>
  
  <?php $data->ratingWidget($this); ?>

  <p class="price">
  <?php echo $data->price ?> грн.
  </p>
  <?php echo $data->statusName; ?>
  </header>
  <h1>
	<?php echo CHtml::link(CHtml::encode($data->product_name), array('/shop/'.$data->page_alias)); ?>
	</h1>
  <footer>
	  <?php 
	    /*foreach($data->char as $char)
	      if($char->char_value != '')
	        echo CHtml::encode($char->char_value) . '; '; */
	    //echo Helpers::truncHTML(500, $data->description);
	    $data->description = str_replace('&nbsp;', ' ', strip_tags($data->description));
	    $len = 400;
	    echo mb_substr($data->description, 0, $len, 'UTF-8') . ((mb_strlen($data->description, 'UTF-8') > $len)?'…':'');
	  ?>
	</footer>

</article>
