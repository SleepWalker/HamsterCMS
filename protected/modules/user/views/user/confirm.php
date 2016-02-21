<?php
/**
 * @var $code код результата проверки емейла
 *    0 - Такого эмейла не существует
 *    1 - Успешная активация
 *    2 - Аккаунт уже активирован
 * @var SiteController $this
 */

switch($code)
{
case 1:
?>
  <h1>Активация аккаунта прошла успешно</h1>
  <p>Теперь вы сможете получать специальные скидки и участвовать в акциях нашего магазина.</p>
  <p>Для продолжения работы с сайтом вернитесь на <a href="/">главную страницу</a>.</p>
  <p>Если вы еще не авторизовались, можете воспользоваться формой входа:</p>
  <p><a href="/user/login">Войти на сайт</a></p>
<?php
  break;
case 2:
?>
  <h1>Активация аккаунта не удалась</h1>
  <p>На ваш почтовый ящик было выслано повторное письмо для активации</p>
<?php
  break;
default:
  throw new CHttpException(404,'Ошибка. Такого емейла не существует, либо он уже активирован');
  break;
}
