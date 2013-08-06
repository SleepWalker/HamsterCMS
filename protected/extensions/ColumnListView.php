<?php 
// TODO: комментарии и описание, вынести скрипт в отдельный файл 
Yii::import('zii.widgets.CListView');
 
class ColumnListView extends CListView
{
 
	public $columns = 3;
	public $columnClass = 'column';
  public $autoScrollMargin = 0;

  public function run()
  {
    // подчистим буфер вывода, что бы убрать из вывода не нужный html
    if (Yii::app()->request->isAjaxRequest)
      while(@ob_end_clean());
    else
    {
      $id=$this->getId();
      ob_start();
?>
$(function() {
  var maxPageNumber = <?php echo $this->dataProvider->pagination->pageCount ?>;
  var nextPageNumber = 2;
  var loading = false;

  $('body').on('click', '#<?php echo $id ?> .pager a', function()
  {
    // сбиваем номер текущей странички
    if(parseInt($(this).text()) == 1)
    {
      nextPageNumber = 2;
    }
    
    $("html, body").animate({ scrollTop: getContainer().offset().top - <?php echo $this->autoScrollMargin; ?>}, 'fast');
  });

  if(getPagerContainer().length == 0) // у нас только одна страничка
    return;

  $(window).scroll(function() {
    // ajax подгрузка должна работать только на первой странице
    if(/<?php echo $this->dataProvider->pagination->pageVar ?>=[2-9]/.test(location.search))
      return;

    $columns = getContainer().children('.<?php echo $this->columnClass; ?>');

    var minOffset = getMinColumnOffset($columns);

    var scrollPosition = $(window).height() + $(window).scrollTop();

    if (scrollPosition > minOffset && nextPageNumber <= maxPageNumber && !loading) 
    {
      loading = true;
      // запрашиваем следующую страницу с результатами
      $.get(location.pathname + '?<?php echo $this->dataProvider->pagination->pageVar ?>=' + nextPageNumber, function(data) {
        $newData = $(data).filter('#<?php echo $id ?>').children('.<?php echo $this->columnClass; ?>');
        if($newData.length > 0)
        {
          nextPageNumber++;
          loading = false;
          $columns.each(function(key){
            $(this).append($newData.eq(key).contents());
          });

          var $pagerContainer = getPagerContainer();
          if(!$pagerContainer.hasClass('passivePager'))
          {
            $pagerContainer.addClass('passivePager');
            $pagerContainer.find('li.selected').removeClass('selected');
          }
        }
      });
    }
  }).scroll();

  // возвращает минимальное расстояние предпоследних блоков в колонках $columns к верху страницы
  function getMinColumnOffset($columns)
  {
    var minOffset = $columns.eq(0).children().eq($columns.eq(0).children().length-2).offset().top;
    $columns.each(function() {
      var $children = $(this).children();
      minOffset = Math.min($children.eq($children.length-2).offset().top, minOffset);
    });
    return minOffset;
  }

  function getContainer()
  {
    return $('#<?php echo $id ?>');
  }

  function getPagerContainer()
  {
    return getContainer().find('.pager');
  }
});
<?php
      $js = ob_get_clean();
      $cs=Yii::app()->getClientScript();
      $cs->registerScript(__CLASS__.'#'.$id, $js);
    }
    parent::run();
  }
 
	//@override)
	public function renderItems()
	{
		$y = 0;
		if($this->columns < 2)
		    return;
	    $this->columns = range(1, $this->columns);
		foreach ($this->columns as $column)
		{			
			echo CHtml::openTag('div',array('class'=> $this->columnClass . ' ' . $this->columnClass . '-'.$column,))."\n";
			$columns = sizeof($this->columns);

			$data=$this->dataProvider->getData();
			if(count($data)>0)
			{
				$owner=$this->getOwner();
				$render=$owner instanceof CController ? 'renderPartial' : 'render';
				foreach($data as $i=>$item)
				{	
					if( ($i+ ($columns - $y)) % $columns == 0)
					{
						$data=$this->viewData;
						$data['index']=$i;
						$data['data']=$item;
						$data['widget']=$this;
						$owner->$render($this->itemView,$data);
					}
				}
			}
			else
				$this->renderEmptyText();
			echo CHtml::closeTag('div');
			$y++;
		}	
	}
 
}
