<article class="blogPost postTeaser">
  <header>
    <?php echo CHtml::link(CHtml::encode($data->name), $data->viewUrl) ?>
  </header>

	<section class="postContent">
    <?php echo array_shift(explode('{%CUT%}', $data->desc)) . ' &hellip;'; ?>
	</section>
  <footer>
    <section class="tags" style="float:right;">
      <?php
        echo CHtml::link('Читать полностью...', $data->viewUrl);
      ?>
    </section>
  </footer>
</article>