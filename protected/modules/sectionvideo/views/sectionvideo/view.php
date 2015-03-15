<?php
/*
$this->breadcrumbs=array(
    $this->module->params->moduleName=>array('index'),
    $model->title,
);
*/
$this->pageTitle = (!empty($model->caption) ? $model->caption . ' - ' : '') . (!empty($model->composition) ? $model->composition . ' - ' : '') . $this->module->params->moduleName;
$model->htmlEncode();
?>

<div class="column-layout">
    <div class="column">
        <?php
        $this->renderPartial('theme.views.viewWidgets.sectionvideo.full', array(
            'data' => $model,
        ));
        ?>

        <div class="comments" id="disqus_thread"></div>
        <script type="text/javascript">
            /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
            var disqus_shortname = 'estrocksection'; // required: replace example with your forum shortname

            /* * * DON'T EDIT BELOW THIS LINE * * */
            (function() {
                var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
                dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
                (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
            })();
        </script>
        <noscript>Please enable JavaScript to view the <a href="https://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>

    </div>
    <div class="column">
        <aside class="related-videos">
            <div class="related-videos__clip">
                <?php $this->widget('hamster.widgets.view.SimpleListView', array(
                    'model' => 'hamster\modules\sectionvideo\models\Video',
                    'amount' => 6,
                    'view' => 'small',
                )); ?>
            </div>
            <div class="related-videos__scrollbar scrollbar">
            </div>
        </aside>
    </div>
</div>

<?= $model->ratingWidget() ?>
