<div class="HTabsContainer">
<menu class="HTabs">
<?php
if(isset($modelId))
{
?>
<a href="" class="active"><?php echo Yii::app()->params->shortName; ?></a>
<?php
}
//FIXME: временное исправление для случаев, когда на сайте отключен вк
if(!empty(Yii::app()->params['vkApiId']))
{
?>
<a href="" onmouseover="$('#vkcomments').hcomments('vk')">Вконтакте</a>
<?php
}
?>
</menu>

<?php
if(isset($modelId))
{
?>
<section class="HCommentInternal">
<?php
$newComment->model_id = $modelId;
$newComment->model_pk = $modelPk;
$socialityUrl = Yii::app()->createUrl('sociality/sociality/index');
$socialityQuery = http_build_query(array('modelId'=>$modelId, 'modelPk'=>$modelPk));

$form = $this->beginWidget('CActiveForm', array(
  'id'=>'HCommentForm',
  'action' => $socialityUrl,
  'enableAjaxValidation'=>true,
  'enableClientValidation'=>true,
  'clientOptions' => array(
    'validateOnSubmit' => true,
  ),
));

if(Yii::app()->user->isGuest)
{
  echo '<div class="row">' . $form->textField($newComment, 'name', array('placeholder' => 'Имя'));
  echo $form->error($newComment,'name') . '</div>';
  echo '<div class="row">' . $form->textField($newComment, 'email', array('placeholder' => 'Емейл (не показывается)'));
  echo $form->error($newComment,'email') . '</div>';
}
echo '<div class="row">' . $form->textArea($newComment, 'comment', array('placeholder' => 'Ваш комментарий...'));
echo $form->error($newComment,'comment') . '</div>';
echo $form->hiddenField($newComment, 'model_id');
echo $form->hiddenField($newComment, 'model_pk');
echo '<div class="row buttons">' . CHtml::ajaxSubmitButton('Отправить', $socialityUrl, array(
  // при успешном запросе очищает форму комментов и 
  // обновляет список комментов
  'success' => new CJavaScriptExpression('function(data) {$("textarea", $("#HCommentForm")).val(null);$.fn.yiiListView.update("HCommentsList",{url: "' . $socialityUrl . '?' . $socialityQuery . '"});}'),
), array('id'=>'submitComment')) . '<span>Работает отправка сообщений по ctrl+enter</span></div>';

$this->endWidget('CActiveForm');

$this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$comments,
	'itemView'=>'_comment',
  'beforeAjaxUpdate'=>'function() {
    $("html, body").animate({scrollTop:$("#HCommentForm").parent(".HCommentInternal").offset().top}, "fast");     
    $("#HCommentForm").next().hide("normal");
  }',
  'afterAjaxUpdate'=>'function() {$("#HCommentForm").next().show("normal");}',
  //'itemsCssClass' => 'gridC photoGrid',
  'id' => 'HCommentsList',
  'summaryText' => '',
  'pager'=>array(
    'cssFile'=>false,
    'header'=>false,
  ),
  'cssFile'=>false,
));
?>
</section>
<?php
}
?>
<?php
if(!empty(Yii::app()->params['vkApiId']))
{
//FIXME: временное исправление для случаев, когда на сайте отключен вк
?>
<section><div id="vkcomments"></div></section>
<?php
}
?>
</div>

<script>
$(function() {
  $("textarea", "#HCommentForm").keydown(function(event) {
    if (event.keyCode == 13 && event.ctrlKey)
      $("#submitComment").click();
  }).autosize();
  $('#HCommentForm').next().find('.yiiPager a').each(function() {
    var prefix = this.href.indexOf('?') > 0 ? '&' : '?';
    this.href += prefix + '<?php echo $socialityQuery  ?>';
  });
});
</script>
