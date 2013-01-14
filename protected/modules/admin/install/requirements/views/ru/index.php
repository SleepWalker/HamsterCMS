<div id="page">

<div id="content">
<h2>Описание</h2>
<p>
Данный скрипт проверяет соответствие конфигурации Вашего веб-сервера требованиям, 
предъявляемым веб-приложениями HamsterCMS и <a href="http://www.yiiframework.com/">Yii</a>.
В частности, проверяются версия РНР и загруженные расширения РНР, а также 
корректность настроек файла php.ini. 
</p>

<h2>Заключение</h2>
<p>
<?php if($result>0): ?>
Поздравляем! Конфигурация Вашего веб-сервера полностью удовлетворяет требованиям HamsterCMS.
<?php elseif($result<0): ?>
Конфигурация Вашего веб-сервера удовлетворяет минимально необходимым требованиям HamsterCMS. Обратите внимание на предупреждения в таблице ниже, если предполагается использование соответствующего функционала. 
<?php else: ?>
К сожалению, конфигурация Вашего веб-сервера не удовлетворяет требованиям HamsterCMS.
<?php endif; ?>
</p>

<h2>Результаты проверки</h2>

<table class="result">
<tr><th>Название</th><th>Итог</th><th>Требуется для</th><th>Пояснение</th></tr>
<?php foreach($requirements as $requirement): ?>
<tr>
	<td>
		<?php echo $requirement[0]; ?>
	</td>
	<td class="<?php echo $requirement[2] ? 'passed' : ($requirement[1] ? 'failed' : 'warning'); ?>">
		<?php echo $requirement[2] ? 'Да' : ($requirement[1] ? 'Нет' : 'Предупреждение'); ?>
	</td>
	<td>
		<?php echo $requirement[3]; ?>
	</td>
	<td>
		<?php echo $requirement[4]; ?>
	</td>
</tr>
<?php endforeach; ?>
</table>

<table>
<tr>
<td class="passed">&nbsp;</td><td>Да</td>
<td class="failed">&nbsp;</td><td>Нет</td>
<td class="warning">&nbsp;</td><td>Предупреждение</td>
</tr>
</table>

</div><!-- content -->

<div id="footer">
<?php echo $serverInfo; ?>
</div><!-- footer -->

</div><!-- page -->
