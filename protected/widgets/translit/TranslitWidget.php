<?php
/**
 * TranslitWidget ads transliteration ability for input fields
 * Adds onblur transliteration and also icon, that will
 * transliterate the value of input tag, that stays earlier in HTML tree
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    hamster.widgets.translit.TranslitWidget
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

namespace application\widgets\translit;

class TranslitWidget extends \CInputWidget
{
    /**
     * Имя поля, значение которого будет транслитирироваться
     */
    public $attribute;

    /**
     * Модель, которой принадлежит поле
     */
    public $model;

    /**
     *  Режим транслитерации url адреса (слеэши не будут удаляться из текста)
     */
    public $urlMode = false;

    public function init()
    {
        $this->registerClientScripts();
    }

    public function run()
    {
        list($name, $id) = $this->resolveNameID();

        $sourceFieldId = \CHtml::activeId($this->model, $this->attribute);

        $options = [
            'urlMode' => $this->urlMode,
        ];

        $js = '$("#' . $sourceFieldId . '").translit(' . \CJavaScript::encode($options) . ');';
        \Yii::app()->getClientScript()->registerScript(__CLASS__ . '#TranslitWidget' . $sourceFieldId, $js);

        echo \CHtml::activeTextField($this->model, $this->attribute);
    }

    protected function registerClientScripts()
    {
        $assetsUrl = \Yii::app()->getAssetManager()->publish(dirname(__FILE__).'/assets');

        $scriptName = YII_DEBUG ? 'translit.jquery.js' : 'translit.jquery.min.js';
        \Yii::app()->getClientScript()->registerScriptFile($assetsUrl . '/' . $scriptName);
    }
}
