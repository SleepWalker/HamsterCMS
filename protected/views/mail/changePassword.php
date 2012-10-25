<h1>Здравствуйте, <?php echo $user->first_name; ?></h1>
<p>Вы получили это письмо, так как для вашего Email было запрошено восстановление пароля. Для продолжения этой процедуры нажмите на кнопку ниже.</p>

<p align="center">
<a href="<?php echo $user->chpassUrl ?>" target="_blank" style="display:inline-block;background-color:#B8DA5C;padding:10px 20px;margin:20px;color:#000000;text-decoration:none;border:1px #000 solid;font-size:25px;">
Восстановить пароль
</a>
</p>

<ul>
<li>Если ваш клиент не поддерживает переход по ссылкам, скопируйте этот адрес и вставьте в адресную строку вашего браузера: <?php echo $user->chpassUrl ?></li>
<li>Если это письмо попало к вам по ошибке, проигнорируйте его.</li>
</ul>

<p>P.S. Это письмо сгенерировано автоматически. Не отвечайте на него</p>