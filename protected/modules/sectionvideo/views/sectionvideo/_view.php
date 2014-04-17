<?php $data->htmlEncode(); ?>
	<article class="videoEntry">
		<header>
			<?php echo CHtml::link(Chtml::image($data->thumbnail, $data->caption), $data->viewUrl) ?>
		</header>

		<div class="videoCaption">
			<h1><?php echo CHtml::link($data->caption, $data->viewUrl) ?></h1>
			<?php if($data->composition) echo CHtml::link($data->composition, $data->viewUrl) ?>
		</div>
	</article>
<?php
