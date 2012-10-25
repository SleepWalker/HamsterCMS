<?php	
/**
 * Filter widget class for shop module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.ShopController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
 
Yii::import('shop.models.*');

/**
 *  Строит информер с продуктами
 *  Описание параметров ниже
 */
class Filter extends CWidget
{
  
  protected $assetsUrl;
  
  public function init()
  {    
    $widgetPath = dirname(__FILE__).DIRECTORY_SEPARATOR;
    
    // регестрируем assets
    $this->assetsUrl = Yii::app()->getAssetManager()->publish($widgetPath.'assets',false,-1,YII_DEBUG);
    $this->registerCssFile('filter.css');
    $this->registerCssFile('tip-twitter.css');
    $this->registerScriptFile('jquery.poshytip.min.js');
    
    if($this->controller->action->id == 'categorie')
    {
      // Добываем id родителей текущей категории
      $curCatModel = $_GET['alias']; // после использования функции в этой переменной будет модуль
      $catIds = Categorie::model()->getParentsCatIds(false, array(), $curCatModel);
      $curCat = $catIds[count($catIds)-1];
      $catIds = implode(",", $catIds);
      
      // Проверяем является ли категория родителем
  	  $sql = 'SELECT count(*) FROM shop_categorie WHERE cat_parent='.$curCat;
  	  $command=Yii::app()->db->createCommand($sql);
  	  $isParent = $command->queryScalar();
  	  if($isParent)
  	  {
  	    $this->widget('shop.widgets.product.Product', array(
          'show' => 'top',
        ));
        return;
      }
     
      $dataProvider=new CActiveDataProvider('CharShema', array(
          'criteria'=>array(
            'condition'=>'t.cat_id IN(' . $catIds . ')', 
          ),
	      )
	    );
	    
	    $models = $dataProvider->getData();
	    echo '<section id="ShopProductFilter">';
	    // убираем GET запрос из url (если такой был)
	    $action = preg_replace('/\?[^\?]*$/','',$_SERVER["REQUEST_URI"]);
	    echo CHtml::beginForm($action, 'GET');
	    
	    // фильтр по цене
	    echo '<h4>Цена</h4>';
	    $model = Shop::model();
	    // Переопределяем атрибуты модели данными из гет запроса
	    $model->attributes = $_GET['Shop'];
	    $model->cat_id = $curCat;
	    $this->widget('shop.widgets.filter.FilterRangeSlider', array(
          'model'=>$model,
          'attribute'=>'priceMin',
          'maxAttribute'=>'priceMax',
          'name'=>'PF',
          // additional javascript options for the slider plugin
          'options'=>array(
              'range'=>true,
              'min'=>$model->minPriceVal,
              'max'=>$model->maxPriceVal,
          ),
      ));
      
      // фильтр по брендам
      foreach($curCatModel->brands as $model)
        $brandList[$model->brand_id] = $model->brand_name;
      if(is_array($brandList))
      {
        echo '<h4>Производители</h4>';
        if(empty($_GET['BF'])) $_GET['BF'] = array_flip($brandList);
        echo CHtml::checkBoxList("BF[]", $_GET['BF'], $brandList);
      }

	    foreach ($models as $model)
	    {
	     if ($model->type == 1 || $model->type == 6) continue; // текстовые или скрытые  поляне обрабатываем
	     echo '<h4>' . $model->char_name . '</h4>';
	     if($model->type == 3 || $model->type == 2 || $model->type == 5)
	     { // поля типа множественный (select/checkbox) и одиночный выбор
	       $chtmlMethodName = $model->type == 5 ? 'radioButtonList' : 'checkBoxList';
	       // для характеристик типа множественный выбор (checkbox) делаем отдельный элемент в массиве запроса
	       // так как там нужно обеспечить сравнения по типу LIKE
	       $inputName = $model->type == 3 ? "CF[m][" . $model->char_id . "]" : "CF[" . $model->char_id . "]";
	       $inputValues = $model->type == 3 ? $_GET['CF']['m'][$model->char_id] : $_GET['CF'][$model->char_id];
	       echo CHtml::$chtmlMethodName($inputName, $inputValues, 
          $model->ddMenuArr['items']);
	     }
	     
	     if($model->type == 4)
	     { // числовые поля, отображаются слайдерами
	      $charModel = new Char;
  	    // Переопределяем атрибуты модели данными из гет запроса
  	    $charModel->min = $_GET['CNF'][$model->char_id][0];
  	    $charModel->max = $_GET['CNF'][$model->char_id][1];
        $charModel->char_id = $model->char_id;
	       $this->widget('shop.widgets.filter.FilterRangeSlider', array(
            'model'=> $charModel,
            'attribute'=>'min',
            'maxAttribute'=>'max',
            'name'=>'CNF[' . $model->char_id . ']',
            // additional javascript options for the slider plugin
            'options'=>array(
                'range'=>true,
                'min'=> $charModel->minValue,
                'max'=> $charModel->maxValue,
            ),
            'htmlOptions' => array(
              'name' => 'CNF[' . $model->char_id . '][]',
            ),
        ));
	     }
	    }
	    echo '<p>' . CHtml::submitButton('Применить', array('name'=>'', 'style'=>'width:114px;'));
	    echo '<br />' . CHtml::button('Сброс', array('style'=>'width:114px;', 'onclick'=>'location.href=' . CJavaScript::encode($action))) . '</p>';
	    echo CHtml::endForm();
	    echo '</section>';
	    
      $filterAlign = Yii::app()->getModule('shop')->params['filterAlign'] ? Yii::app()->getModule('shop')->params['filterAlign'] : 'right';
	    
	    // Ajax 0бновление формы
	    $js = '
	      var tipsArr = [];
	      // при каждой отправке ajax прячем все всплывающие подсказки
        $("body").bind("hideTips", function(){
          while(tipSelector = tipsArr.shift())
            tipSelector.poshytip("hide");
        });
        
	      $("body").on("change", "#ShopProductFilter input", function() {
	        var form = $(this).parents("form");
	        // Инициализируем подсказку
	        var tipSelector = ($(this).is("[type=checkbox]") || $(this).is("[type=radio]")) ? $(this).next("label") : $(this).parent();
	        // уничтожаем подсказки этого элемента (на тот случай, если они еще не уничтожены)
	        tipSelector.poshytip("destroy");
	        tipSelector.poshytip({
          	className: "tip-twitter",
          	showOn: "none",
          	alignTo: "target",
          	alignX: "' . $filterAlign . '",
          	alignY: "center",
          	offsetX: 10
          });
          
	        $.ajax({
            url: form.prop("action"),
            data: form.serialize() + "&ajax=1",
            type: "POST",
            context: $(this),
            success: function (data) {
              // инициируем событие для скрытия подсказок
              $("body").trigger("hideTips");
              tipsArr.push(tipSelector); // добавляем обьект текущей подсказки в массив с активными подсказками                                                                                                                     
              
              tipSelector.poshytip("update", data);
              tipSelector.poshytip("show");
              
            },
          });
	      });
	    ';
	    Yii::app()->getClientScript()->registerScript(__CLASS__.'#none', $js);
    }
  }
  
  protected function registerScriptFile($fileName,$position=CClientScript::POS_END)
  {
    Yii::app()->getClientScript()->registerScriptFile($this->assetsUrl.'/js/'.$fileName,$position);
  }
  
  protected function registerCssFile($fileName)
  {
    Yii::app()->getClientScript()->registerCssFile($this->assetsUrl.'/css/'.$fileName);
  }
}
