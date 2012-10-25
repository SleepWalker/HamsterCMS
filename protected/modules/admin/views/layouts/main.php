<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="robots" content="noindex,nofollow">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link type="text/css" rel="StyleSheet" href="<?php echo $this->adminAssetsUrl; ?>/css/admin.css" />
<title><?php echo CHtml::encode($this->pageTitle); ?></title>

</head>
<body>
<div class="header_big" id="header">
<div class="header_bg">
<div id="menu">
<a href="/" class="menu_top"><img src="<?php echo $this->adminAssetsUrl; ?>/images/menu_main.png" class="menu_top" alt="Главная" /></a>
<a href="#" class="menu_top"><img src="<?php echo $this->adminAssetsUrl; ?>/images/menu_config.png" alt="Настройки" /></a>
<a href="<?php echo Yii::app()->createUrl('/site/logout'); ?>" class="menu_top"><img src="<?php echo $this->adminAssetsUrl; ?>/images/menu_logout.png" alt="Выход из админ панели" /></a>
<a href="<?php echo Yii::app()->createUrl('admin'); ?>/"><img src="<?php echo $this->adminAssetsUrl; ?>/images/humsterLogo.png" id="logo" alt="Hamster CMS" /></a>
<a href="#" class="menu_top"><img src="<?php echo $this->adminAssetsUrl; ?>/images/menu_modules.png" alt="Модули" /></a>
<a href="#" class="menu_top"><img src="<?php echo $this->adminAssetsUrl; ?>/images/menu_extensions.png" alt="Расширения" /></a>
<a href="/admin/tmpls" class="menu_top"><img src="<?php echo $this->adminAssetsUrl; ?>/images/menu_design.png" alt="Дизайн" /></a>
</div>

<!--div id="ajax_status_wrapper">
<div class="ajax_status">
<img src="<?php echo $this->adminAssetsUrl; ?>/images/ajax.gif" />
</div>
</div-->

<div id="menu1" class="ddmenu">
<?php 
echo CHtml::link('Бекап', Yii::app()->createUrl('admin/backup'));
echo CHtml::link('Логи', Yii::app()->createUrl('admin/logs'));
echo CHtml::link('Настройки Hamster', Yii::app()->createUrl('admin/config'));
?>
</div>

<div id="menu4" class="ddmenu">
<?php 
$modulesInfo = $this->modulesInfo;
$enabledModules = $this->enabledModules;
echo CHtml::link('Управление страницами', Yii::app()->createUrl('admin/page'));
if(count($modulesInfo))
  foreach($modulesInfo as $moduleId=>$moduleConfig)
    if(array_key_exists($moduleId, $enabledModules))
      echo CHtml::link($moduleConfig['title'], Yii::app()->createUrl('admin/' . $moduleId));
?>
</div>

<div id="menu5" class="ddmenu">
<?php //$content->printExtMenu(); ?>
</div>

<div id="menu6" class="ddmenu">
<?php //$content->printTmplsMenu(); ?>
</div>

</div>
<div class="header_bottom"></div>
</div>

<div class="wrapper">
<div class="block_wrapper">
<?php 
foreach($this->aside as $blockName => $block)
{
    ?>
<div class="block">
  <div class="block_top_light">
    <div class="block_bottom">
      <div class="block_bottom_light">
        <h5><?php echo $blockName; ?></h5>
        <div id="side_menu">
          <?php
          foreach ($block as $blockItemUrl => $blockItem)
          {
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
/*function designBlock($title, $content, $menu) {
?>
<div class="block">
<div class="block_top_light">
<div class="block_bottom">
<div class="block_bottom_light">
<h5><?php echo $title ?></h5>
<?php
if($menu) {
?>
<div id="side_menu">
<?php
}
?>
<?php echo $content ?>
<?php
if($menu) {
?>
</div>
<?php
}
?>
</div>
</div>
</div>
</div>
<?php
}*/
?>

</div>
<div class="content_wrapper">
<div id="message_block"></div>
<div class="tabs">
<?php echo $this->tabs; ?>
</div>
<?php 
echo '<h1>';
echo CHtml::encode($this->pageTitle);
echo '</h1>';
echo $content;
?>

</div>

</div>
<div id="footer">
<div class="footer_line"></div>
</div>

<script type="text/javascript" src="<?php echo $this->adminAssetsUrl; ?>/js/admin.js"></script> 
</body>
</html>
