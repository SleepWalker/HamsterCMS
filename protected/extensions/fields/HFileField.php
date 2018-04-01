<?php
namespace ext\fields;

use hamster\models\UploadedFile;

/**
 * HFileField виджет для облегчения загрузки и обработки файлов через ajax
 */
class HFileField extends \CInputWidget
{
    /**
     * @var string $assetsUrl url папки со скриптами, стилями и графикой виджета
     */
    public $assetsUrl;

    /**
     * The relation, if provided, should point to hamster/models/UploadedFile model
     *
     * @var string the name of the relation attribute in model
     */
    public $relation;

    public function init()
    {

        list($this->name, $this->id) = $this->resolveNameId();

        $this->registerClientScript();
    }

    public function run()
    {
        if ($this->hasModel()) {
            $model = $this->model;
            $attribute = $this->attribute;
            $src = null;

            if ($this->relation) {
                $file = $this->model->{$this->relation};

                if ($file) {
                    if ($file instanceof UploadedFile) {
                        $src = $file->getAdminThumbUrl();
                    } else {
                        throw new \CException('Relation must be instance of UploadedFile');
                    }
                }
            } else {
                // @deprecated some old deprecated staff, that, probably, won't work
                if (!empty($model[$attribute]) && !is_array($model[$attribute])) {
                    // Выводим картинку (только в случае если картинка одна,
                    // тоесть атрибут модели не содержит массив
                    $src = $model->src();
                }
            }

            if ($src) {
                $this->htmlOptions['style'] = 'display:none;';
                echo '<div class="renewImage">';
                echo \CHtml::image($src, $attribute, ['id' => $attribute . '_tag']);
                echo \CHtml::link('Удалить', '#', ['class' => 'icon_delete']);
                echo '</div>';

                $js = '$(".renewImage .icon_delete").on("click", function() {
                    var $container = $(this).parent().parent();
                    $(this).parent().remove(); // удалили картинку и кнопку по которой был клик
                    $container.find("input[type=hidden]").val("delete");
                    $container.find("input[type=file]")
                        .show()
                        .after("Изображение окончательно удалится/изменится после отправки формы")

                    return false;
                });';

                \Yii::app()->getClientScript()
                    ->registerScript('renewImage', $js, \CClientScript::POS_END);
            }

            echo \CHtml::activeFileField($this->model, $this->attribute, $this->htmlOptions);
        } else {
            echo \CHtml::fileField($this->name, $this->value, $this->htmlOptions);
        }

    }

    private function registerClientScript()
    {
        $this->assetsUrl = \Yii::app()->getAssetManager()->publish(dirname(__FILE__) . '/assets', false, -1, YII_DEBUG);
        $cs = \Yii::app()->clientScript;
        $cs->registerCoreScript('jquery');
        $cs->registerScriptFile($this->assetsUrl . '/js/fileUploader.js', \CClientScript::POS_END);
        $initJs = '$("#' . $this->id . '").fileUploader()';
        $cs->registerScript(__CLASS__ . '#' . $this->id, $initJs);
    }
}
