<?php
/**
 * Загружает изображения видео ролика с Youtube или из кэша,
 * ресайзит их и сохраняет на локальном диске
 */

namespace sectionvideo\components;

class VideoImage
{
    protected $_videoId;

    public function __construct($videoId)
    {
        $this->_videoId = $videoId;
    }

    public function get($sizeId = null)
    {
        $thumbnails = $this->getThumbnails();

        if (empty($thumbnails)) {
            return '';
        }

        if (isset($thumbnails['medium']['url'])) {
            // пока что medium достаточно для нас
            return $thumbnails['medium']['url'];
        } else {
            return isset($thumbnails['maxres']['url']) ? $thumbnails['maxres']['url'] : $thumbnails['high']['url'];
        }

    }

    protected function getThumbnails()
    {
        return \Yii::app()->getModule('sectionvideo')->externalVideo->get($this->_videoId)->getThumbnails();
    }
}
