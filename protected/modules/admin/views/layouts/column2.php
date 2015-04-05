<?php $this->beginContent('/layouts/main'); ?>

<div class="block_wrapper">
<?php
foreach($this->aside as $blockName => $block) {
    ?>
<div class="block">
  <div class="block_top_light">
    <div class="block_bottom">
      <div class="block_bottom_light">
        <h5><?php echo $blockName; ?></h5>
        <div id="side_menu">
          <?php
          foreach ($block as $blockItemUrl => $blockItem) {
           echo CHtml::link($blockItem, $blockItemUrl);
          }
          ?>
        </div>
      </div>
    </div>
  </div>
</div>
    <?php
}
?>
<?php
if(Yii::app()->menuMap->hasSuggestions) {
    ?>
<div class="block">
  <div class="block_top_light">
    <div class="block_bottom">
      <div class="block_bottom_light">
        <h5>Разделы</h5>
        <div id="side_menu">
          <?php
            Yii::app()->menuMap->suggest();
          ?>
        </div>
      </div>
    </div>
  </div>
</div>
    <?php
}
?>


</div><!-- .block_wrapper -->

<div class="content_wrapper">

<div id="message_block"></div>
<?php $this->widget('\admin\widgets\AdminModuleTabs', [
    'tabsMap' => $this->getTabs(),
    'actionId' => $this->action->id,
]); ?>

<?php
// Yii Flash
$flashes = array('success', 'fail', 'info', 'error');
foreach ($flashes as $flash) {
    if (Yii::app()->user->hasFlash($flash)) {
        echo '<div class="' . $flash . 'FlashBlock">' . Yii::app()->user->getFlash($flash) . '</div>';
    }
}


echo '<h1>';
echo $this->pageTitle;
if ($this->pageActions) {
    echo '<div id="pageActions">' . $this->pageActions . '</div>';
}
echo '</h1>';
echo $content; ?>
</div><!-- .content_wrapper -->
<?php $this->endContent(); ?>
