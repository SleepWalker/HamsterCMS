<?php
/**
 * Creates a new widget based on the given class name and initial properties.
 * Priority order:
 * 1. Actual parameter: $properties
 * 2. Specific widget class factory config
 * 3. CJui common factory config
 * 4. If skin is defined (as above), use skin properties from 
 *    widget specific skin file in active Yii theme
 *
 * Source: http://www.yiiframework.com/forum/index.php?/topic/23354-common-properties-for-jui-widgets/page__pid__113756#entry113756
 * @param CBaseController $owner the owner of the new widget
 * @param string $className the class name of the widget. This can also be a path alias (e.g. system.web.widgets.COutputCache)
 * @param array $properties the initial property values (name=>value) of the widget.
 * @return CWidget the newly created widget whose properties have been initialized with the given values.
 */
class EWidgetFactory extends CWidgetFactory
{
  public function createWidget($owner,$className,$properties=array())
  {
    $widgetName=Yii::import($className);
    if (isset($this->widgets['CJuiWidget']) && is_subclass_of($widgetName, 'CJuiWidget'))
    {
      // Merge widget class specific factory config and the $properties parameter
      // into $properties.
      if(isset($this->widgets[$widgetName]))
        $properties = $properties===array() ? $this->widgets[$widgetName] : CMap::mergeArray($this->widgets[$widgetName],$properties);

      // для админки у нас особые условия!
      if(method_exists($owner, 'getModule') && $owner->module->id == 'admin') {
        $properties['themeUrl'] = $owner->module->assetsUrl . '/css/jui';
      }

      // Merge CJui common factory config and the $properties parameter
      // into the $properties parameter of parent call.
      return parent::createWidget($owner,$className,CMap::mergeArray($this->widgets['CJuiWidget'],$properties));
    }

    return parent::createWidget($owner,$className,$properties);
  }
}
