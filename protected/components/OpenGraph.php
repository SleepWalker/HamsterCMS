<?php

namespace hamster\components;

class OpenGraph extends \CApplicationComponent
{
    public function registerMeta(array $meta)
    {
        $meta = $this->remapMeta($meta);

        foreach ($meta as $name => $content) {
            $this->getClientScript()->registerMetaTag($content, $name, null, [], $name);
        }
    }

    private function remapMeta(array $meta)
    {
        $keyMap = [
            'type' => 'type',
            'url' => 'url',
            'title' => 'title',
            'description' => 'description',
            'image' => 'image',
            'updatedTime' => 'updated_time'
        ];

        $newMeta = [
            'og:site_name' => \Yii::app()->name,
        ];
        foreach ($meta as $key => $value) {
            if (!isset($keyMap[$key])) {
                throw new \DomainException('Unsupported og:tag: ' . $key);
            }

            $value = $this->preprocess($key, $value);

            $key = $keyMap[$key];

            $newMeta['og:' . $key] = $value;
        }

        return $newMeta;
    }

    /**
     * Валидация и если нужно, предварительная обработка значения мета
     * @return string
     */
    private function preprocess($key, $value)
    {
        $method = 'preprocess' . ucfirst($key);

        if (method_exists($this, $method)) {
            return $this->$method($value);
        }

        return $value;
    }

    public function preprocessTitle($value)
    {
        if (empty($value)) {
            throw new \DomainException("The title can not be empty");
        }

        return $value;
    }

    private function preprocessUrl($value)
    {
        if (strpos($value, 'http') !== 0) {
            if (strpos($value, '/') !== 0) {
                throw new \DomainException('The og:url should be absolute');
            }

            $value = \Yii::app()->createAbsoluteUrl($value);
        }

        return $value;
    }

    public function preprocessImage($value)
    {
        return $this->preprocessUrl($value);
    }

    private function preprocessUpdatedTime($value)
    {
        if (preg_match('/^\d\d\d\d\-\d\d\-\d\d \d\d:\d\d:\d\d$/', $value)) {
            $value = strtotime($value);
        }

        if (!preg_match('/\d+/', $value)) {
            throw new \DomainException('The og:updated_time should be unix timestamp');
        }

        return $value;
    }

    private function getClientScript()
    {
        return \Yii::app()->getClientScript();
    }
}
