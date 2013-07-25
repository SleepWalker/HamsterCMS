<h1 align="center">Добро пожаловать в установщик HamsterCMS!</h1>

<?php
echo '<div class="form" style="width:400px;margin:0 auto;">'.CHtml::beginForm('/db')
.CHtml::label('Выберите вариант установки', 'installType')
.'<br>'
.CHtml::radioButtonList('installType', 'quick', array(
  'quick' => 'Быстрый (минимальная настройка Hamster)',
  'detail' => 'Медленный (настройка всех параметров Hamster)',
))
.'<p align="center">'
.CHtml::submitButton('Продолжить')
.'</p>'
.CHtml::endForm()
.'</div>'
;
?>
<h2 align="center">Проверка на соответствие требованиям HamsterCMS</h2>
<?php
require(Yii::getPathOfAlias('application.modules.admin.install.requirements') . DIRECTORY_SEPARATOR . 'index.php')
?>
