<?php

namespace ext\fields;

class HFormInputElement extends \CFormInputElement
{
    private $_label;
    private $_required;

    public function getRequired()
    {
        if ($this->_required !== null) {
            return $this->_required;
        } else {
            return $this->getParent()->getModel()->isAttributeRequired($this->getAttributeName());
        }
    }

    public function setRequired($value)
    {
        $this->_required = $value;
    }

    public function getLabel()
    {
        if ($this->_label !== null) {
            return $this->_label;
        } else {
            return $this->getParent()->getModel()->getAttributeLabel($this->getAttributeName());
        }
    }

    public function setLabel($value)
    {
        $this->_label = $value;
    }

    protected function evaluateVisible()
    {
        return $this->getParent()->getModel()->isAttributeSafe($this->getAttributeName());
    }

    protected function getAttributeName()
    {
        $attribute = $this->name;
        \CHtml::resolveName($this->getParent()->getModel(), $attribute); // filtering [a][b]attribute

        return $attribute;
    }
}
