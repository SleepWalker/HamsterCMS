<h1>Здравствуйте, <?php echo $user->first_name; ?></h1>
<h2>Поздравляем с успешной регистрацией на shop.pwn-zone.com</h2>
<p>Для активации аккаунта нажмите кнопку ниже.</p>

<p align="center">
<a href="<?php echo $user->confirmUrl ?>" target="_blank" style="display:inline-block;background-color:#B8DA5C;padding:10px 20px;margin:20px;color:#000000;text-decoration:none;border:1px #000 solid;font-size:25px;">Подтвердить Email</a>
</p>

<ul>
<li>Если ваш клиент не поддерживает переход по ссылкам, скопируйте этот адрес и вставьте в адресную строку вашего браузера: <?php echo $user->confirmUrl ?></li>
<li>Если это письмо попало к вам по ошибке, проигнорируйте его.</li>
</ul>

<p>P.S. Это письмо сгенерировано автоматически. Не отвечайте на него</p>