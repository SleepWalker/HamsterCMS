<h1>Настройка кэша</h1>
<?php
echo '<div class="form">'.CHtml::beginForm()
.CHtml::label('Выберите тип кэша', 'cacheType')
.'<br>'
.CHtml::radioButtonList('cacheType', $data['cacheType'], array(
  'filesystem' => 'Кеширование в файловой системе (по умолчанию)',
  'memcache' => 'Кеширование с помощью memcache',
  'memcached' => 'Кеширование с помощью memcached',
))
.'<div style="display:none"><h2>Введите данные сервера</h2>'
.CHtml::label('Хост сервера', 'memcache[host]')
.CHtml::textField('memcache[host]')

.CHtml::label('Порт', 'memcache[port]')
.CHtml::textField('memcache[port]')
.'</div>'
.CHtml::submitButton('Отправить')
.CHtml::endForm()
.'</div>'
;

?>
<script>
$(function() {
  $('input[type=radio]').change(function() {
    $this = $(this);
    if($this.val() != 'filesystem')
    {
      $this.parent().next('div').find('input').prop('required', 'required');
      $this.parent().next('div').show('fast');
    }else{
      $this.parent().next('div').find('input').removeProp('required');
      $this.parent().next('div').hide('fast');
    }
  });
});
</script>
