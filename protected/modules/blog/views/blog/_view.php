<article class="blogPost postTeaser">
  <header>
    <?php echo CHtml::link(CHtml::encode($data->title), $data->viewUrl) ?>
    <?php echo CHtml::link($data->img(), $data->viewUrl) ?>
  </header>

	<section class="postContent">
    <?php echo array_shift(explode('{%CUT%}', $data->content)) . ' &hellip;'; ?>
	</section>
  <footer>
    <section class="tags" style="float:right;">
      <?php
        echo CHtml::link('Читать полностью...', $data->viewUrl);
      ?>
    </section>
    <section role="tags" class="tags">
      <?php
        foreach($data->tagsArr as $tag)
        {
          echo CHtml::link($tag . '<span onclick="location.href=\'' . Tag::model()->tagRssViewUrl($tag) . '\'; return false;" title="Подписаться на RSS тега: '.$tag.'">' . $tag . '</span>', Tag::model()->tagViewUrl($tag));
        }
      ?>
    </section>
  </footer>
</article>

<?php /*
<?php //echo CHtml::encode($data->user->first_name); ?>
<b><?php echo CHtml::encode($data->getAttributeLabel('add_date')); ?>:</b>
    <?php echo CHtml::encode($data->add_date); ?>
    */
    ?>