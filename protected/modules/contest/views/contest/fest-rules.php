<?php
$md = new CMarkdownParser();
ob_start();
?>
# Условия участия в концерте «У колі друзів» 2018

Концерт «У колі друзів» рассчитан на учеников музыкальных школ, колледжей, училищ, а так же других молодых исполнителей, которые стремятся проявить и реализовать свои способности в музыкальном направлении.

## Основатели и организаторы фестиваля

1. Киевский городской методический центр учреждений культуры и учебных заведений
2. Киевская городская секция современной эстрадной и рок музыки
3. Школа джазового и эстрадного искусств (ШДЭИ)

## Порядок проведения фестиваля

Фестиваль пройдет в два этапа:

1. **Отборочный этап**. Прием заявок до 29.03.
2. **Концерт**, который пройдет 13го апреля.

Вся свежая информация будет доступна на нашем сайте, а так же, при необходимости, отправлена каждому конкурсанту на его Email, указанный при подаче заявки.

## Условия фестиваля

Для участия в фестивале вам необходимо приготовить две композиции (для учеников до 11 лет — одну) в эстрадном/рок стиле. После прохождения участником отборочного этапа есть вероятность, что к участию в фестивале будет отобрано только одно произведение.

Общее время выступления участника не должно превышать **8 минут**. Для ансамблей — **12 минут**.

Если Вам помогают преподаватели, учтите, что больше одного преподавателя на сцену не выпустим! Исключением может быть "Сольное исполнение в сопровождении ансамбля", т.к. в этом случае будет оцениваться только солист.

В фестивале можно выделить следующие номинации:

* Инструментальное соло
* Вокальное соло
* Вокально-инструментальный ансамбль

Все участники поделены по возрасту:

* до 11 лет
* 11-14 лет
* 15-17 лет
* 18 лет и старше

Возможны следующие форматы номеров:

* Сольное исполнение (без сопровождения)
* Сольное исполнение в сопровождении концертмейстера
* Сольное исполнение под минус
* Сольное исполнение в сопровождении ансамбля
* Ансамблевое исполнение

## Участие в фестивале

Отборочный этап фестиваля будет проходить онлайн. Для участия необходимо предоставить демо-записи конкурсной программы. Это можно сделать оставив <?= CHtml::link('онлайн-заявку', ['apply']) ?>, указав в ней ссылки на демо-записи (видео можно бесплатно загрузить на [Youtube](http://youtube.com)). Если по каким-то причинам у Вас не выходит загрузить демо-записи в интернет, оставьте онлайн заявку с той информацией, которую Вы можете предоставить и <?= CHtml::link('свяжитесь', ['/site/contact']) ?> с нами.

Все, кто пройдут отборочный этап, будут проинформированы о дате и времени саундчека и выступления по email или по телефону, которые они указали в онлайн заявке.

Все выступления будут записаны на видео и размещены на сайте конкурса. Если вы против того, что бы ваше видео выкладывалось в интернет, пожалуйста, предупредите об этом заранее.

<?= CHtml::link('Отправить заявку', ['apply'], [
    'class' => 'button button--primary button--inner-border',
]); ?>

## Наши контакты

**E-mail**: contest@estrocksection.kiev.ua<br>
<?= CHtml::link('Написать онлайн', ['/site/contact']) ?>

<?= $md->safeTransform(ob_get_clean()) ?>
