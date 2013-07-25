<h1>Настройка базы данных</h1>
<?php
echo '<div class="form">';
if($error)
  echo '<p><span class="error">Ошибка подключения к бд:<br><b>' . $error . '</b></span></p>';

echo CHtml::beginForm()
. CHtml::label('Хост базы данных', 'db[host]')
. CHtml::textField('db[host]', $data['host'], array('required' => 'required'
))

. CHtml::label('Имя базы данных', 'db[name]')
. CHtml::textField('db[name]', $data['name'], array('required' => 'required'
))

. CHtml::label('Пользователь', 'db[username]')
. CHtml::textField('db[username]', $data['username'], array('required' => 'required'
))

. CHtml::label('Пароль', 'db[password]')
. CHtml::passwordField('db[password]', $data['password'], array('required' => 'required'
))

. CHtml::submitButton('Отправить')
. CHtml::link(CHtml::button('Назад'), '/')

. CHtml::endForm()
. '</div>'
;
