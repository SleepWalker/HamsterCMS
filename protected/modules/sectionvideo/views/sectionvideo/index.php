<?php
$this->breadcrumbs=array(
	$this->module->params->moduleName,
);

$this->pageTitle = $this->module->params->moduleName;
?>

<!--h1><?php echo $this->module->params->moduleName ?></h1-->

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
	'summaryText' => '',
	'pager'=>array(
		'cssFile'=>false,
		'header'=>false,
	),
	'cssFile'=>false,
	'ajaxUpdate' => false,
	// 'enableHistory' => true,
)); ?>

<br />
<h2>Облако тегов</h2>
<section id="tagCloud" role="tags" class="tags">
<?php
	$maxTags=20;
	$tags = $dataProvider->model->findTagWeights($maxTags);
	
	if(count($tags))
		foreach($tags as $tag=>$weight)
		{
			echo CHtml::link(CHtml::encode($tag), $dataProvider->model->tagViewUrl($tag));
		}
	else
		echo 'Нет тегов.';
?>
</section>



<script type="text/template" id="modalWindowTmpl">
	<div class="md-overlay">
		<div class="md-modal md-effect-1" id="">
			<div class="md-content">
				<!--h3>{title}</h3-->
				<div>
					{content}
					<button class="md-close">Close me!</button>
				</div>
			</div>
		</div>
	</div>
</script>

<?php
$assetsUrl = Yii::app()->getAssetManager()->publish(dirname(__FILE__).DIRECTORY_SEPARATOR.'assets',false,-1,YII_DEBUG);

$cs = Yii::app()->clientScript;
$cs->registerCoreScript('history');
$cs->registerScriptFile($assetsUrl . '/js/classie.js', CClientScript::POS_END);
$cs->registerScriptFile($assetsUrl . '/js/modalEffects.js', CClientScript::POS_END);
$cs->registerCssFile($assetsUrl . '/css/modal.css');

ob_start();
?>
var tmpl = $('#modalWindowTmpl').html();
var homeLoc = '<?php echo Yii::app()->createUrl('sectionvideo/sectionvideo/index') ?>';
var initTitle = '<?php echo $this->pageTitle ?>';
$('.videoEntry').on('click', 'a', function() {
	if(History.enabled)
	{
		History.pushState({ href: normalizeUrl(this.href) }, 'Video', normalizeUrl(this.href));

		return false;
	}
});

History.Adapter.bind(window,'statechange', function(e) {
	var state = History.getState();
	var data = state.data;

	if('href' in data)
		showVideo(data.href);
});

if(homeLoc != location.pathname)
{
	if(History.enabled)
	{
		History.replaceState({ href: normalizeUrl(location.href) }, 'Video');
		History.Adapter.trigger(window, 'statechange');
	}else{
		// Для ие < 10
		showVideo(location.href);
	}
}

function goHome()
{
	if(History.enabled)
		History.pushState({}, 'Video', normalizeUrl(homeLoc));
	else // for ie < 10
		location.href = homeLoc;
	$('head > title').text(initTitle);
	$('body').css({overflow: 'visible'});
}

$('body').on('close', '.md-modal', goHome);

function showVideo(href)
{
	$.ajax(clearUrlSearch(href) + '?ajax=1', {
		type: 'get',
		dataType: 'json',
		success: function(data) {
			// TODO: если окошко маленькое - перевести его в выравнивание по центру
			// translateX
			var $overlay = $(tmpl.replace('{content}', data.content));
			$('head > title').text(data.title);
			var $md = $overlay.find('.md-modal');
			$overlay.appendTo('body');
			$('body').css({overflow: 'hidden'});
			setTimeout( function() {
				$md.addClass('md-show');
				$overlay.addClass('md-show');
			}, 25 );
		},
		error: function() {
			goHome();
		}
	});
}

function normalizeUrl(url)
{
	return clearUrlSearch(url) + decodeURIComponent(location.search);
}

function clearUrlSearch(url)
{
	if(url.indexOf('?') != -1)
		url = url.slice(0, url.indexOf('?'));

		return url;
}
<?php
$js = ob_get_clean();
$cs->registerScript(__FILE__, $js);