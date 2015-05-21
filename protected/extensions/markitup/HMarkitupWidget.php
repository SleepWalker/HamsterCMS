<?php
/**
 * HMarkitupWidget adds {@link http://markitup.jaysalvat.com/ markitup} as a form field widget.
 *
 * В этой версии добавленная кнопка для загрузки изображений на сервер.
 *
 * @author Sviatoslav Danylenko <dev@udf.su>
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @version 1.1
 * @link http://code.google.com/p/yiiext/
 * @link http://markitup.jaysalvat.com/
 *
 * @depends AjaxDialogWidget
 */

namespace ext\markitup;

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'EMarkitupWidget.php');

class HMarkitupWidget extends \EMarkitupWidget
{

    public $settings = 'hmarkdown';
    public $theme = 'hamster';

    public $options = [];

    /**
     * Init widget.
     *
     * @todo отключать доп кнопку если нету поля attachments
     */
    public function init()
    {
        $this->options = \CMap::mergeArray([
            'previewAutoRefresh' => true,
            'previewParserPath' => \Yii::app()->createUrl('admin/test/md'),
            'previewParserVar' => 'code',
        ], $this->options);
        parent::init();

        if (!$this->hasModel()) {
            return;
        }

        if (!$this->model->hasAttribute('attachments')) {
            //throw new CException('Модель должна иметь поле attachments. (тип TEXT)');
        } else {

            // callback для добавления изображения в редактор
            $callbackId = 'imgUpload_' . uniqid();
            ob_start();
?>
            var $obj = $(obj);
            $obj.closest('.hDialog').dialog('close');
            miu.pushImage($('#<?php echo $this->id ?>'), {src: imgSrc}, true);
<?php

            /**
             * @var obj кнопка "Вставить в редактор"
             */
            $callbackJs = 'window.' . $callbackId . '= function(obj, imgSrc) {' . ob_get_clean() . '};';

/*
        $attachments = array(
            0 => array(
                'name' => '520b0c9f09d46.jpg',
                'alt' => 'Альтернативный текст',
                'title' => 'Название изображения',
            ),
        );
 */

            $this->model->attachments = !empty($this->model->attachments) ? unserialize($this->model->attachments) : array(); // TODO: поидее это должно быть где-то в модели

            // $imagesUri = \HIUBehavior::getUploadsBaseUrl() . '/'. \Image::getAttachmentsPath(\Yii::app()->controller->action->id);
            $imagesUri = \HIUBehavior::getUploadsBaseUrl() . '/';

            // уже загруженные изображения
            if (count($this->model->attachments) > 0) {
                ob_start();
                foreach ($this->model->attachments as $id => $attachment) {
?>
                miu.pushImage($('#<?php echo $this->id ?>'), {src: '<?php echo $imagesUri . DIRECTORY_SEPARATOR . $attachment['name'] ?>', id: <? echo $id ?>});
<?php
                }
                $callbackJs .= ob_get_clean();
            }

            \Yii::app()->controller->widget('ext.jui.AjaxDialogWidget', array(
                'selectors' => array("#markItUp{$this->id} li.hmdImageUpload"),
                'options' => array(
                    'title' => 'Загрузка изображений',
                ),
                'ajaxOptions' => array(
                    'url' => \Yii::app()->createUrl('admin/upload/image', array('callback' => $callbackId)),
                ),
            ));

            \Yii::app()->clientScript->registerScript(__CLASS__ . '#' . $callbackId, $callbackJs);
        }
    }
}
