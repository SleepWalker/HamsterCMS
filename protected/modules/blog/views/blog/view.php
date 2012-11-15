<?php
$this->breadcrumbs=array(
	$this->module->params->moduleName=>array('index'),
	$model->title,
);

$cs = Yii::app()->clientScript;

$socialScript = "
(function(d, s, id) {
  var div = d.createElement('div');
  div.id = 'fb-root';
  d.body.insertBefore(div, d.body.firstChild);
  var js, fjs = d.getElementsByTagName(s)[0]; 
  if (d.getElementById(id)) {return;} 
  js = d.createElement(s); js.id = id; 
  js.src = '//connect.facebook.net/en_US/all.js#xfbml=1' 
  fjs.parentNode.insertBefore(js, fjs); 
 }(document, 'script', 'facebook-jssdk'));
 
 (function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/plusone.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
  })();
  
  window.___gcfg = {
    lang: 'ru',
    parsetags: 'onload'
  };
  
  !function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src='https://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document,'script','twitter-wjs');
  
  VK.init({apiId: " . Yii::app()->params['vkApiId'] . ", onlyWidgets: true});
  
  VK.Widgets.Like('vk_like', {type: 'vertical', height: 24}); 
  VK.Widgets.Comments('vkcomments', {limit: 10, attach: '*'});
";
$cs->registerScriptFile('http://userapi.com/js/api/openapi.js?43', CClientScript::POS_END);
$cs->registerScript(__CLASS__ . '#SocialScript', $socialScript, CClientScript::POS_END);

// SEO and Sociality meta
$desc = strip_tags(mb_substr(array_shift(explode('{%CUT%}', $model->content)), 0, 200, 'UTF-8'));
$cs->registerMetaTag($desc, 'description');
$cs->registerMetaTag($desc, NULL, NULL, array('property' => 'og:description'));
$cs->registerMetaTag($model->title, NULL, NULL, array('property' => 'og:title'));
$cs->registerMetaTag('product', NULL, NULL, array('property' => 'og:type'));
//$cs->registerMetaTag('Ссылка на материал', NULL, NULL, array('property' => 'og:url'));
$cs->registerMetaTag(Yii::app()->name, NULL, NULL, array('property' => 'og:site_name'));
$imgSrc = Yii::app()->createAbsoluteUrl($model->src('thumb'));
$cs->registerMetaTag($imgSrc, NULL, NULL, array('property' => 'og:image'));
$cs->registerLinkTag('image_src', NULL, $imgSrc);

$this->pageTitle = $model->title;
?>

<article class="blogPost">
  <header>
    <h1><?php echo $model->title; ?></h1>
    <?php echo $model->img() ?>
  </header>

	<section class="postContent">
    <?php 
$this->beginWidget('application.widgets.lightbox.HLightBox');
echo str_replace('{%CUT%}', '', $model->content); 
$this->endWidget('application.widgets.lightbox.HLightBox');
?>
	</section>
  <footer>
    <div class="soc_buttons"> 
      <div class="sbutton"><a href="https://twitter.com/share" class="twitter-share-button" data-count="vertical" data-via="">Tweet</a></div> 
      <div class="sbutton" style="padding-top:1px;"><fb:like send="false" style="width:49px;overflow:hidden;" layout="box_count" show_faces="true"></fb:like></div> 
      <div class="sbutton" style="padding-top:2px;"><g:plusone size="tall"></g:plusone></div> 
      <div class="sbutton" style="padding-top:7px;"><div id="vk_like"></div></div> 
    </div>
    <section role="tags" class="tags" style="float:right;">
      <?php
        foreach($model->tagsArr as $tag)
        {
          echo CHtml::link($tag . '<span onclick="location.href=\'' . Tag::model()->tagRssViewUrl($tag) . '\'; return false;" title="Подписаться на RSS тега: '.$tag.'">' . $tag . '</span>', Tag::model()->tagViewUrl($tag));
        }
      ?>
    </section>
  </footer>
</article>
<section id="vkcomments" style="clear:both;"></section>
