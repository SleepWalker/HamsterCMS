<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo CHtml::encode($this->pageTitle); ?></title>
<link rel="alternate" type="application/rss+xml" title="Pst.studio: Статьи" href="/blog/rss" />
<link rel="image_src" href="<?php echo Yii::app()->request->baseUrl; ?>/images/logo_pst.png" /> 
<link type="text/css" rel="StyleSheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/style.css" />
</head>
<body>
<div class="page_wrapper">
<div id="header">
<a href="/" id="logo"><img src="/images/logo.png" alt="pststudio.com" /></a>
<div id="portfolio">
<a href="/page/portfolio">Портфолио</a>
</div>
<div id="articles">
<a href="/blog">Блог</a>
</div>
<div id="services">
<a href="/page/services">Услуги</a>
</div>
<div id="contact">
<a href="/site/contact">Контакт</a>
</div>
</div>
<script type="text/javascript">
function hover(obj, hover) {
    if(hover) {
        obj.style.backgroundPosition = '-127px 0';
    }else{
        obj.style.backgroundPosition = '0 0';
    }
}
var menuItems = document.getElementById('header').getElementsByTagName('div');
for (var i = 0; i < menuItems.length; i++) {
    (function(obj) {obj.onmouseover = function() {hover(obj, true)}})(menuItems[i]);
    (function(obj) {obj.onmouseout = function() {hover(obj, false)}})(menuItems[i]);
}
</script>
<div class="content_wrapper">
<?php echo $content; ?>
<?php 
  if($this->id == 'page' && empty($_GET['path'])) {
    echo '<br /><br /><h2 align="center">Последние статьи</h2>';
    $this->widget('blog.widgets.posts.Posts'); 
  } // endif($this->action->id != 'page') 
?>
</div>

<div class="footer">
<a href="/">PST</a> © 2010
</div>

</div>
</body>
</html>