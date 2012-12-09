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
  // задаем стандартные значения, удобные для hamster
  public $minRating = 1;
  public $maxRating = 5;
  public $ratingStepSize = 1;
  public $allowEmpty = false;
  public $titles = array(1=>'Ужасно', 'Плохо', 'Нормально', 'Хорошо', 'Отлично');
  public $cssFile = false;
  public $blur = 'function(value, link){
    if (window.pauseTips) return;
    var $tip = $("#rating_success");
    $tip.html($tip.data("vCount") || "");
}';
  public $focus = 'function(value, link){
    if (window.pauseTips) return;
    var $tip = $("#rating_success");
    $tip.data("vCount", $tip.data("vCount") || $tip.html().replace(/[^\d]/g, "")*1);
    $tip.html(link.title || "value: "+value);
  }';
public $callbackUrl;

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

    // делаем так, что бы id был действительно уникальным
    $this->htmlOptions['id'] = $id = $id.uniqid();

    echo CHtml::openTag('span',$this->htmlOptions)."\n";
    if($this->readOnly)
    {
      $this->renderStaticStars($id,$name);
    }else{
      if(!isset($callback))
      {
        if(!isset($this->callbackUrl))
          throw new CException('Для работы рейтинга необходимо указать параметр callbackUrl');

        $this->callback = 'function(){
          url = "' . $this->callbackUrl . '";
          jQuery.get(url, {id: "'.$this->model->primaryKey.'", val: $(this).val()}, function(data) {
            var $tip = $("#rating_success");
            $tip.html(data.answer);           
            if(data.status == "success") 
              $tip.data("vCount", $tip.data("vCount") + 1);

            var totalVotes = "(" + $tip.data("vCount") + ")";
            window.pauseTips = setTimeout(function() {$tip.html(totalVotes);window.pauseTips=false},3000);
      }, "json");}';
      }

      $this->registerClientScript($id);
      $this->renderStars($id,$name);
    }		
    echo "</span>";
    if(isset($this->model))
    {
?>
<span id="rating_success" style="text-indent:5px; vertical-align: 3px;">(<?php echo $this->model->votesCount ?>)</span>
<?php
    }
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
