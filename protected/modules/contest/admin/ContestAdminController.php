<?php
/**
 * Admin action class for blog module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.modules.sectionvideo.admin.SectionvideoAdminController
 * @copyright  Copyright &copy; 2013 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

class ContestAdminController extends \admin\components\HAdminController
{
    /**
     * @return меню для табов
     */
    public function tabs()
    {
        return array(
            ''  => 'Все видео',
            'update'  => array(
                'name' => 'Редактирование видео',
                'display' => 'whenActive',
            ),
            'create'  => array(
                'name' => 'Добавить видео',
                'display' => 'index',
            ),
        );
    }

    /**
     *  Выводит таблицу всех товаров
     */
    public function actionIndex()
    {
        $model=new Video('search');
        $model->unsetAttributes();
        if (isset($_GET['Video'])) {
            $model->attributes=$_GET['Video'];
        }

        $tags = $model->tagModel()->findAll();
        $tagsMenu = array();
        foreach ($tags as $tag) {
            array_push($tagsMenu, $tag->name);
        }
        $this->aside = CMap::mergeArray($this->aside, array('Теги' => $tagsMenu));

        $this->render('table', array(
            'dataProvider'=> $model->search(),
            'options' => array(
             'filter'=>$model,
            ),
            'columns'=>array(
                array(
                        'name'=>'image',
                        'value'=>'Chtml::image($data->thumbnail, $data->caption, array("width" => 100))',
                        'type'=>'raw',
                        'filter'=>'',
                ),
                'caption',
                /*
                array(
                        'name'=>'cat_id',
                        'value' => '$data->cat->name',
                        'filter'=> Categorie::model()->catsList,
                ),
                array(
                        'name'=>'status',
                        'type'=>'raw',
                        'value' => '$data->statusName',
                        'filter'=> Post::getStatusNames(),
                ),
                array(
                        'name'=>'user_search',
                        'value' => '$data->user->first_name',
                ),
                // Using CJuiDatePicker for CGridView filter
                // http://www.yiiframework.com/wiki/318/using-cjuidatepicker-for-cgridview-filter/
                // http://www.yiiframework.com/wiki/345/how-to-filter-cgridview-with-from-date-and-to-date-datepicker/
                // http://www.yiiframework.com/forum/index.php/topic/20941-filter-date-range-on-cgridview-toolbar/
                array(
                        'name'=>'add_date',
                        'type' => 'datetime',
                        'filter' => $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                            'model'=> $model,
                            'attribute'=>'date_add_from',
                            'language' => 'ru',
                            'defaultOptions' => array(
                                'showOn' => 'focus',
                                'showOtherMonths' => true,
                                'selectOtherMonths' => true,
                                'changeMonth' => true,
                                'changeYear' => true,
                                'showButtonPanel' => true,
                                'autoSize' => true,
                                'dateFormat' => "yy-mm-dd",
                            )
                        ), true)
                        .
                        $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                            'model'=> $model,
                            'attribute'=>'date_add_to',
                            'language' => 'ru',
                        ), true),
                ),
                array(
                        'name'=>'edit_date',
                        'type' => 'datetime',
                        'filter' => $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                            'model'=> $model,
                            'attribute'=>'date_edit_from',
                            'language' => 'ru',
                        ), true)
                        .
                        $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                            'model'=> $model,
                            'attribute'=>'date_edit_to',
                            'language' => 'ru',
                        ), true),
                ),
                */
            ),
        ));
    }
}
?>
