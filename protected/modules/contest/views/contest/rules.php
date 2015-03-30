<?php
$md = new CMarkdownParser();
ob_start();
?>
# Правила всеукраинского конкурса «Рок єднає нас» 2015

Всеукраинский конкурс современной эстрадной и рок музыки «Рок єднає нас» 2015 рассчитан на учеников музыкальных школ, колледжей, училищ, а так же других молодых исполнителей, которые стремятся проявить и реализовать свои способности в музыкальном направлении.

Полная версия правил, на украинском языке доступна [здесь](/uploads/contest/2015/rock_2015.pdf).

## Порядок проведения конкурса

Конкурс пройдет в три этапа:

1. **Отборочный этап**. Прием заявок: 01.03-01.05.
2. **Финал**, который пройдет в 23го мая 2015го года.
3. **Награждение и галла-концерт** лучших исполнителей 24 мая 2015 года.

Вся свежая информация будет доступна на нашем сайте, а так же отправлена каждому конкурсанту на его Email.

## Условия конкурса

Тема конкурса **Эстрадная и рок музыка 60-70х годов**. Для участия в финале конкурса вам необходимо приготовить две композиции в эстрадном/рок стиле, одна из которых должна быть написана в 60-70х годах (исключения допускаются только для акустических инструментов).

Общее время выступления конкурсанта не должно превышать **12 минут**. Для ансамблей — **15 минут**.

Если Вам помогают преподаватели, учтите, что больше одного преподавателя на сцену не выпустим! :)

В конкурсе определены следующие номинации:

* Инструментальное соло
* Инстр. ансамбль
* Вокальное соло
* Вокально-инстр. ансамбль

Все участники поделены по возрасту:

* до 10 лет
* 11-14 лет
* 15-17 лет
* 18 лет и больше
* В случае ансамбля возраст участников не учитывается

Возможны следующие форматы номеров:

* Сольное исполнение (без сопровождения)
* Сольное исполнение в сопровождении концертмейстера
* Сольное исполнение под минус
* Сольное исполнение в сопровождении ансамбля
* Ансамблевое исполнение

## Участие в конкурсе

Отборочный этап конкурса будет проходить онлайн. Для участия необходимо предоставить демо-записи конкурсной программы. Это можно сделать оставив <?= CHtml::link('онлайн-заявку', array('apply')) ?>, указав в ней ссылки на демо-записи (видео можно бесплатно загрузить на [Youtube](http://youtube.com)). Если по каким-то причинам у Вас не выходит загрузить демо-записи в интернет, оставьте онлайн заявку с той информацией, которую Вы можете предоставить и <?= CHtml::link('свяжитесь', array('site/contact')) ?> с нами.

Все, кто пройдут в финал, будут проинформированы о дате и времени саундчека и выступления по email или по телефону, которые они указали в онлайн заявке.

Ввиду того, что проведение финала конкурса требует определенных затрат на организацию, участие в **финале** конкурса платное:

* Сольный исполнитель — 150 грн.
* Ансамбли — 75 грн. с каждого члена ансамбля.

Все выступления будут записаны на видео и размещены на сайте конкурса (по желанию исполнителя).

<?= CHtml::link('Записаться на конкурс', array('apply'), array(
    'class' => 'button button--primary button--inner-border',
)); ?>

## Наши контакты

**E-mail**: contest@estrocksection.kiev.ua<br>
<?= CHtml::link('Написать онлайн', array('site/contact')) ?>

<?= $md->safeTransform(ob_get_clean()) ?>
