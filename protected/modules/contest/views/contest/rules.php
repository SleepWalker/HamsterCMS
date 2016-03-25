<?php
$md = new CMarkdownParser();
ob_start();
?>
# Правила всеукраинского конкурса «Рок єднає нас» 2016

Всеукраинский конкурс современной эстрадной и рок музыки «Рок єднає нас» 2016 рассчитан на учеников музыкальных школ, колледжей, училищ, а так же других молодых исполнителей, которые стремятся проявить и реализовать свои способности в музыкальном направлении.

## Основатели и организаторы конкурса

1. Киевский городской методический центр учреждений культуры и учебных заведений
2. Киевская городская секция современной эстрадной и рок музыки
3. Школа джазового и эстрадного исксств (ШДЭИ)

## Порядок проведения конкурса

Конкурс пройдет в два этапа:

1. **Отборочный этап**. Прием заявок до 20.03.
2. **Финал**, который пройдет в 22-23го апреля 2016го года.

Вся свежая информация будет доступна на нашем сайте, а так же отправлена каждому конкурсанту на его Email, указанный при подаче заявки.

## Условия конкурса

Тема конкурса **Эстрадная и рок музыка 70-80х годов**. Для участия в финале конкурса вам необходимо приготовить две композиции в эстрадном/рок стиле. Как минимум одна из композиций должна быть написана в 70-80х годах.

Общее время выступления конкурсанта не должно превышать **12 минут**. Для ансамблей — **15 минут**.

Если Вам помогают преподаватели, учтите, что больше одного преподавателя на сцену не выпустим! :)

В конкурсе определены следующие номинации:

* Инструментальное соло
* Вокальное соло
* Вокально-инструментальный ансамбль

Все участники поделены по возрасту:

* до 10 лет
* 11-14 лет
* 15-17 лет
* 18 лет и старше

Возможны следующие форматы номеров:

* Сольное исполнение (без сопровождения)
* Сольное исполнение в сопровождении концертмейстера
* Сольное исполнение под минус
* Сольное исполнение в сопровождении ансамбля
* Ансамблевое исполнение

Количество номинаций может меняться в зависимости от количества конкурсантов ущаствующих в конкурсе.

## Участие в конкурсе

Отборочный этап конкурса будет проходить онлайн. Для участия необходимо предоставить демо-записи конкурсной программы. Это можно сделать оставив <?= CHtml::link('онлайн-заявку', ['apply']) ?>, указав в ней ссылки на демо-записи (видео можно бесплатно загрузить на [Youtube](http://youtube.com)). Если по каким-то причинам у Вас не выходит загрузить демо-записи в интернет, оставьте онлайн заявку с той информацией, которую Вы можете предоставить и <?= CHtml::link('свяжитесь', ['/site/contact']) ?> с нами.

Все, кто пройдут в финал, будут проинформированы о дате и времени саундчека и выступления по email или по телефону, которые они указали в онлайн заявке.

Ввиду того, что проведение финала конкурса требует определенных затрат на организацию, участие в **финале** конкурса платное:

* Сольный исполнитель — 150 грн.
* Ансамбли — 75 грн. с каждого члена ансамбля.

Все выступления будут записаны на видео и размещены на сайте конкурса (по желанию исполнителя).

<?= CHtml::link('Записаться на конкурс', ['apply'], [
    'class' => 'button button--primary button--inner-border',
]); ?>

## Наши контакты

**E-mail**: contest@estrocksection.kiev.ua<br>
<?= CHtml::link('Написать онлайн', ['/site/contact']) ?>

<?= $md->safeTransform(ob_get_clean()) ?>
