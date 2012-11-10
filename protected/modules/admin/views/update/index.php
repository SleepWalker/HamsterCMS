  <p><b style="color:red">Внимание!</b> функция автообновления расчитана на использование разработчиками. Нажимая на кнопку "Запустить обновление" вы берете всю ответственность за возможные последствия на себя</p>

<?php 
// =========== Страница обновления бд
if($this->action->id == 'db') { ?>
  <p><b style="color:purple">К обновлению:</b> <br>
  <?php echo implode('<br>', $updateList); 

$this->pageTitle = Yii::app()->name . ' - Обновление базы данных';
}

// =========== Страница обновления файлов
if($this->action->id == 'index') { ?>
  <b style="color:red">К удалению:</b><br>
  <?php echo implode('<br>', $deleteList); ?>
  <p><b style="color:green">К обновлению:</b> <br>
  <?php echo implode('<br>', array_keys($updateList)); 

$this->pageTitle = Yii::app()->name . ' - Обновление';
}
    echo '<br><br>' . CHtml::beginForm() . 
    CHtml::submitButton('Запустить обновление', array('name'=>'update')) .
    CHtml::endForm();
