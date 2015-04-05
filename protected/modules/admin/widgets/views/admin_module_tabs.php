<?php
/**
 * @var array $links
 */
?>
<div class="tabs">
    <?php
    foreach ($links as $link) {
        echo \CHtml::link($link['text'], $link['url'], $link['htmlOptions']);
    }
    ?>
</div>
