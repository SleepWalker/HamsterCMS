<?php $this->beginContent('email_layout'); ?>

Здравствуйте, {{fullName}}

поздравляем с прохождением отборочного этапа {{contestName}}!<?php /*

Ваша конкурсная программа:

1. {{firstComposition}}
2. {{secondComposition}} */ ?>

Список всех конкурсантов, порядок чековки и выступления смотрите у нас на сайте.

По ссылке ниже вы можете отредактировать данные вашей заявки:

[{{confirmationUrl}}]({{confirmationUrl}})

Обратите внимание на правильность написания вашего имени и исполняемых композиций. Еще раз напомним, что имя и фамилия должны быть на украинском языке. Именно эти данные будут использоваться для обьявления выступающих.

**Если вы играете под минус, отправьте в ответ на это письмо файлы минусов (в прикреплениях или ссылку по которой можно скачать).**

Если у вас возникли вопросы, пишите на [contest@estrocksection.kiev.ua](mailto:contest@estrocksection.kiev.ua)

<?php $this->endContent(); ?>
