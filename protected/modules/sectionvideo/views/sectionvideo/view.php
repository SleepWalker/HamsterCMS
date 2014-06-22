<?php
/*
$this->breadcrumbs=array(
	$this->module->params->moduleName=>array('index'),
	$model->title,
);
*/
$this->pageTitle = (!empty($model->caption) ? $model->caption . ' - ' : '') . (!empty($model->composition) ? $model->composition . ' - ' : '') . $this->module->params->moduleName;
$model->htmlEncode();
$musicians = $model->musicians;
?>
<article class="videoEntryFull">
	<header>
		<?php echo $model->videoCode; ?>
	</header>
	<section class="videoContent">
		<div class="videoCaption">
			<h1><?php echo $model->caption ?></h1>
			<?php if($model->composition): ?>
				<b><?php echo $model->composition ?></b>
			<?php endif; ?>
		</div>
		<aside>
			<?php if(count($musicians) == 1 && $musicians[0]->teacher): ?>
				<p>Педагог: <br /><?= CHtml::link($musicians[0]->teacher->shortName, $musicians[0]->teacher->viewUrl) ?></p>
			<?php endif; ?>
			<p>Мероприятие: <br /><?= $model->eventLink ?></p>
			<p><?= $model->ratingWidget() ?></p>
			<section role="tags" class="tags">
				<?php
				foreach($model->tagsArr as $tag)
				{
					echo CHtml::link($tag, $model->tagViewUrl($tag));
				}
				?>
			</section>
		</aside>
		<div class="videoDescription">		
			<?php 
			if(count($musicians) > 1)
			{
				?>
				<p><b>Исполняют:</b></p>
				<ul>
				<?php
				foreach ($musicians as $musician) {
					$html = $musician->name;

					if(!empty($musician->class))
						$html .= ', ' . $musician->class .' кл.';

					if(!empty($musician->instrument))
						$html .= " ({$musician->instrument})";

					if(!empty($musician->teacher))
						$html .= '<br /><small>Преподователь: '.CHtml::link($musician->teacher->fullName, $musician->teacher->viewUrl).'</small>';
					?>
					<li><?php echo $html ?></li>
					<?php
				}
				?>
				</ul>
				<?php
			}
			$this->beginWidget('CMarkdown', array('purifyOutput'=>true));
			echo $model->description;
			$this->endWidget('CMarkdown');
			?>
		</div>
	</section>
	<footer>
		<?php
		$this->widget('application.modules.sociality.widgets.HLike', array(
			'imgSrc' => $model->thumbnail,
			'description' => $model->description,
			'title' => $model->caption . ': ' . $model->composition,
			));
		$this->widget('application.modules.sociality.widgets.HComment', array(
			'model' => $model,
			));
			?>
	</footer>
</article>
