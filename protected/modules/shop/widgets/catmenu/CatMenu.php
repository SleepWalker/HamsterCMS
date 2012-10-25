<?php	
/**
 * Widget class that displays shop categories menu
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.widgets.catmenu.CatMenu
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
 
/**
*  Возвращает список тегов A с ссылками на категории выше нулевого уровня
*  Так же можно задать id категории $catId, для которой нужно построить дерево
**/
class CatMenu extends CWidget
{
  public $catId = false; // категория, для которой строить дерево
  public function init()
  {
  
    Yii::import('shop.models.*');
    
    $cacheId = __CLASS__ . $this->catId;
    
    $catMenu = Yii::app()->cache->get($cacheId);
    
    if ($catMenu === false)
    {
      $cats = Categorie::model()->findAll(array(
        'order'=>'cat_sindex'
      ));
       
     $attParent = 'cat_parent';
     $attId = 'cat_id';
     /**
     *  $tree[0] - само дерево категорий
     *  $tree[1+] - вспомогательные элементы массива, которые используются при генерации дерева
     *
     *  $tree[0][catId][0] - категория с catId
     *  $tree[0][catId][1+] - дети категории с catId
     **/
     foreach ($cats as $model) {
        // Добавлем элемент в общий список
        if($model->cat_parent != 0)
          $tree[ $model[$attId] ][0] = '<a href="/shop/categorie/' . $model->cat_alias . '">' . $model->cat_name . '</a>';
        
        if($model[$attParent] == 0)
        {
          // Добавляем массив родителя в дерево категорий
          $tree[0][] = &$tree[ $model[$attId] ];  
        }
        else
        {
          // Добавляем дитя в массив родителя
          if( !is_array($tree[ $model[$attParent] ]) )
            $tree[ $model[$attParent] ][0] = ''; // так как массива нету, значит мы еще не добрались до родителя. Создадим для него пустой элемент.
          $tree[ $model[$attParent] ][] = &$tree[ $model[$attId] ];
        }
      } 
      
      if($this->catId) $tree[0] = array($tree[$this->catId]);
      //$tree = array_values($tree[0]); // реиндексируем массив
      $tree = $tree[0];
      
      ob_start();
      $this->catTreeParse($tree);
      $catMenu = ob_get_clean();
      
      Yii::app()->cache->set($cacheId, $catMenu, 60*30); // кешируем менюшку на пол часа  
    }
    echo $catMenu;
  }
  
  /***********************
  * #catTreeParse - строит дерево из массива
  ***********************/
  protected function catTreeParse($tree, $level = -1) {
    foreach ($tree as $i => $item) {
      if (!is_array($item)) // Если не массив - значит это категория, принтим ее html код
      {
        // блочим категории третьего уровня (временно)
        if($level < 2)
          echo $item;
      }
      else
      { // У этой категорий есть дети, парсим ее массив
        //if ($level >= 0 && $i == 1) echo '<ul level=' . ($level+1) . '>'; // Очень умное условие для вставки открывающих тегов многоуровневых списков
        $this->catTreeParse($item, $level+1);
      }
    }
    //if ($level >= 0 && $i > 1) echo '</ul>'; // Очень умное условие для вставки закрывающих тегов многоуровневых списков
  }
}
