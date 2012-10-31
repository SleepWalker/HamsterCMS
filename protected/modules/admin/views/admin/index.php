<?php
$this->pageTitle = 'Админка';
?>
<h2>Модули</h2><hr />
<section class="gridLayout">
<?php
$menu = Yii::app()->menuMap->getMenu('hamsterModules');
$modulesInfo = $this->modulesInfo;
if(is_array($menu))
  foreach($menu as $label => $route)
  {
    $moduleId = array_pop(explode('/', $route[0]));
?>
  <article>
    <h3><?php echo CHtml::link($label, $route); ?></h3>
    <p>
      <?php echo $modulesInfo[$moduleId]['description']; ?>
    </p>
  </article>
<?php
  }
?>
</section>
<h2>Настройки</h2><hr />
<section class="gridLayout">
<?php
$menu = Yii::app()->menuMap->getMenu('hamsterConfig');
$modulesInfo = $this->modulesInfo;
if(is_array($menu))
  foreach($menu as $label => $route)
  {
    $moduleId = array_pop(explode('/', $route[0]));
?>
  <article>
    <h3><?php echo CHtml::link($label, $route); ?></h3>
    <p>
      <?php echo $modulesInfo[$moduleId]['description']; ?>
    </p>
  </article>
<?php
  }
?>
</section>
<br /><br /><br /><br /><br /><br />
<div style="text-align:right;">
<a href="https://github.com/Pststudio/HamsterCMS" target="_blank"><img src="<?php echo $this->adminAssetsUrl; ?>/images/icon_git.png" alt="Мы на github.com" /></a>
<a href="/LICENSE" target="_blank"><img src="<?php echo $this->adminAssetsUrl; ?>/images/gplv3-88x31.png" alt="GPLv3" /></a>
<a href="http://www.yiiframework.com" target="_blank"><img src="<?php echo $this->adminAssetsUrl; ?>/images/yii-powered.png" alt="Yii Powered" /></a>
</div>
<?php
Yii::app()->clientScript->registerCoreScript('jquery');
?>
