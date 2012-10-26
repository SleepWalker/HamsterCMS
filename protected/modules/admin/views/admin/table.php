<?php
/**
 *  Вьюха, отображающая данные модели в виде таблицы
 *
 *  Параметры:
 *  string $preTable - строка, которая будет печататься перед таблицей
 *  bool $disableButtons - отключает все кнопки
 *  array $buttons - массив, в котором находятся настройки кнопок (см. CGridView 'buttons')
 *  так же в этой вьюхе есть несколько стандартных кнопок: update, delete, create, more, view
 *  настройки этих кнопок можно переопределять, передавая массив с ихним именем, к примеру:
 *  ...
 *  'buttons' => array(
 *    'more' => array(
 *      'visible' => '$data->hasMore',
 *    ),
 *  ),
 *  ...
 *  
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.ShopController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
//$pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);
if(!is_array($buttons)) 
  $buttons = array('update', 'delete', 'view');
  
if(!is_array($options)) 
  $options = array();
  
if($disableButtons) 
  $buttons = array();
else
{
  $updateButton = array(  
    'update'=>array(
      //'label'=>'...',     // text label of the button
      'url'=>'Yii::app()->createUrl("' . $this->actionPath . 'update/" . $data->primaryKey)',       // a PHP expression for generating the URL of the button
      'imageUrl'=> $this->adminAssetsUrl . '/images/icon_edit.png',  // image URL of the button. If not set or false, a text link is used
      //'options'=>array(), // HTML options for the button tag
      //'click'=>'...',     // a JS function to be invoked when the button is clicked
      //'visible'=>'',   // a PHP expression for determining whether the button is visible
    )
  );
  
  $deleteButton = array(
    'delete'=>array(
      'imageUrl'=> $this->adminAssetsUrl . '/images/icon_delete.png',
      'url'=>'Yii::app()->createUrl("' . $this->actionPath . 'delete/" . $data->primaryKey)',
    )
  );
  
  $printButton = array(
    'print'=>array(
      'imageUrl'=> $this->adminAssetsUrl . '/images/icon_print.png',
      'url'=>'Yii::app()->createUrl("' . $this->actionPath . 'print/" . $data->primaryKey)',
    )
  );
  
  $viewButton = array(
    'view'=>array(
      'url'=>'method_exists($data, "getViewUrl") ? $data->viewUrl : ""',
      'options'=>array(
        'target'=>'_blank',
      ),
      'imageUrl'=> $this->adminAssetsUrl . '/images/icon_view.png',
      'visible'=>'method_exists($data, "getViewUrl")',
    )
  );
  
  $moreButton = array(
    'more'=>array(
      'url'=>'Yii::app()->createUrl("' . $this->actionPath . 'more/" . $data->primaryKey)',
      'imageUrl'=> $this->adminAssetsUrl . '/images/icon_table.png',
    )
  );
  
  $buttArr = array();
  $buttCol = array(
    'class'=>'CButtonColumn',
  );
  
  foreach($buttons as $buttonName => $button)
  {
    $curButtonSettings = array();
    if(is_array($button))
    {
      $curButtonSettings[$buttonName] = $button;
      if(is_array(${$buttonName . 'Button'}))
      {
        $curButtonSettings[$buttonName] = array_merge(
          ${$buttonName . 'Button'}[$buttonName],
          $curButtonSettings[$buttonName]
        );
      }
      $button = $buttonName;
    }else
      $curButtonSettings = ${$button . 'Button'};
    
    $buttArr = array_merge(
      $buttArr,
      $curButtonSettings
    );
    $buttCol['template'] .= '{' . $button . '}';
  }
  
  $buttCol['htmlOptions'] = array(
    'width' => 16*count($buttons),
  );
  $buttCol['buttons'] = $buttArr;
}

// Назначаем размер страницы провайдера
$dataProvider->pagination->pageSize = Yii::app()->params['defaultPageSize'];


$defOpts = array(
	'dataProvider'=>$dataProvider,
	'columns'=>$columns,
  'pager'=>array(
    'cssFile'=>false,
    'header'=>false,
  ),
  'cssFile'=>false,
  'beforeAjaxUpdate' => 'startLoad',
  'afterAjaxUpdate' => 'stopLoad',
  'enableHistory' => true,
  //'ajaxUpdate' => false,
);

if($buttCol)
  $defOpts['columns'][] = $buttCol;

echo $preTable;
$this->widget('zii.widgets.grid.CGridView', array_merge(
  $defOpts,
  $options
));
?>
