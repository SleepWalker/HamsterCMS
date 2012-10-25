<?php
/**
 * EStarRating class file.
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    shop.ShopController
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
 
 /**
  * Основное отличие этого класса от CStarRating заключается в том, что если CStarRating - readOnly
  * Мы отображаем статические картинки и не подключаем скриптов
  */

class EStarRating extends CStarRating
{
	/**
	 * Executes the widget.
	 * This method registers all needed client scripts and renders
	 * the text field.
	 */
	public function run()
	{
		list($name,$id)=$this->resolveNameID();
		if(isset($this->htmlOptions['id']))
			$id=$this->htmlOptions['id'];
		else
			$this->htmlOptions['id']=$id;
		if(isset($this->htmlOptions['name']))
			$name=$this->htmlOptions['name'];
    
    echo CHtml::openTag('span',$this->htmlOptions)."\n";
    if($this->readOnly)
    {
      $this->renderStaticStars($id,$name);
    }else{
      $this->registerClientScript($id);
      $this->renderStars($id,$name);
    }		
    echo "</span>";
	}

	/**
	 * Renders static stars.
	 */
	protected function renderStaticStars($id, $name)
  {
    for($i = 0; $i < $this->value; $i++)
      echo '<div id="' . $id . '_' . $i . '" class="star-rating rater-0 star-rating-applied star-rating-readonly star-rating-on"><a>' . $i . '</a></div>';
    for($i; $i < $this->maxRating; $i++)
      echo '<div id="' . $id . '_' . $i . '" class="star-rating rater-0 star-rating-applied star-rating-readonly"><a>' . $i . '</a></div>';    
  }
}