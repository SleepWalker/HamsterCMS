<?php
$md = new CMarkdownParser();
ob_start();
?>
###Правила всеукраинского конкурса
#«Рок єднає нас» 2015

Всеукраинский конкурс современной эстрадной и рок музыки «Рок єднає нас» 2015 рассчитан на учеников музыкальных школ, училищ, а так же других молодых людей, которые стремятся проявить и реализовать свои способности в музыкальном направлении.

Полная версия правил, на украинском языке доступна по [адресу](http://estrocksection.kiev.ua/uploads/contest/2015/rock_I_2015.pdf).

## Порядок проведения конкурса

Конкурс пройдет в три этапа:

1. Отборочный этап — проводится <?= CHtml::link('онлайн', array('apply')) ?> или оффлайн.
2. Финал, который пройдет в мае 2015го года
3. Награждение и галла-концерт лучших исполнителей

Вся свежая информация будет доступна на нашем сайте, а так же отправлена каждому конкурсанту на его Email.

## Условия конкурса

Тема конкурса **Эстрадная и рок музыка 60-70х годов**. Для участия в финале конкурса вам необходимо приготовить две композиции в эстрадном/рок стиле, одна из которых должна быть написана в 60-70х годах (исключения допускаются только для акустических инструментов). Общее время выступления конкурсанта не должно превышать **8 минут** (без учета подготовки к выступлению). Если Вам помогают педагоги, учтите, что больше одного педагога на сцену не выпустим! :)

В конкурсе определены следующие номинации:

* Акустические инструменты
* Электро-инструменты
* Вокал
* Духовые инструменты
* Инструментальные ансамбли (2-6 человек)
* Вокально-инструментальные ансамбли (2-6 человек)

Все участники поделены по возрасту:

* 8-11 лет
* 12-14 лет
* 15-17 лет
* 18 лет и больше
* Ансамбли размером 2-6 человек. Возраст членов ансамбля не учитывается

Возможны следующие форматы номеров:

* Сольное исполнение
* Сольное исполнение в сопровождении концертмейстера
* Сольное исполнение под минус
* Сольное исполнение в сопровождении ансамбля
* Ансамблевое исполнение

## Участие в конкурсе

Для участия необходимо предоставить демо-записи конкурсной программы. Это можно сделать либо отправив <?= CHtml::link('онлайн-заявку', array('apply')) ?>, указав в ней ссылки на демо-записи (например Youtube, Soundcloud) или передав нам диск (оффлайн регистрация).

Для участия в этом конкурсе Вам необходимо <?= CHtml::link('отправить онлайн-заявку', array('apply')) ?> в которой указать ссылки на демо-записи Вашей концертной программы.

***тут будет информация о взносе. Возможно у групп будет другой тариф***

Все, кто пройдут в финал, будут проинформированы о дате и времени саундчека и выступления по email или телефону.

Все выступления будут записаны на видео и размещены на сайте конкурса (по желанию исполнителя).

## Контакты

E-mail: konkurs@estrocksection.kiev.ua<br>
[Написать онлайн](/site/contact)

<?= $md->safeTransform(ob_get_clean()) ?>
