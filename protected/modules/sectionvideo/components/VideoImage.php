<?php
/**
 * Загружает изображения видео ролика с Youtube или из кэша,
 * ресайзит их и сохраняет на локальном диске
 */

namespace sectionvideo\components;

use \CException;

use \Google_Client;
use \Google_Service_YouTube;

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
        $youtube = new Google_Service_YouTube($this->getGoogleClient());

        $listResponse = $youtube->videos->listVideos("snippet", array('id' => $this->_videoId));

        if (empty($listResponse)) {
            throw new CException(get_class($this)."::getThumbnails(): No data for specified video, probably wrong id");
        }

        $video = $listResponse[0];

        return $video['snippet']['thumbnails'];
    }

    protected function getGoogleClient()
    {
        $client = new Google_Client();
        $client->setApplicationName("estrocksection.kiev.ua");
        $client->setDeveloperKey("AIzaSyD7QiC2AO4PUtiMRN9i5SfZOAhZLvSnGzw");

        return $client;
    }
}
