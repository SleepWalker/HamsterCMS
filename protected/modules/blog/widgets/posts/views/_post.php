<article>
  <header>
    <?php echo CHtml::link($data->img(), $data->viewUrl) ?>
    <?php echo CHtml::link(CHtml::encode($data->title), $data->viewUrl) ?>
  </header>
  <?php echo mb_substr(strip_tags(str_replace('{%CUT%}', '',$data->content)),0,200, 'UTF-8') . ' &hellip;'; ?>
</article>