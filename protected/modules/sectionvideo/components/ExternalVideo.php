<?php

namespace sectionvideo\components;

class ExternalVideo extends \CApplicationComponent
{
    private $googleClient;

    private $videos = [];

    public function init()
    {
        $this->googleClient = $this->createGoogleClient();
    }

    public function create($videoId)
    {
        // NOTE: ссылка и id от одного и того же видео создадут дубликаты обьектов
        if (!isset($this->videos[$videoId])) {
            $this->videos[$videoId] = new \sectionvideo\components\YoutubeVideo($videoId, $this->googleClient);
        }

        return $this->videos[$videoId];
    }

    public function createGoogleClient()
    {
        $client = new \Google_Client();
        $client->setApplicationName("estrocksection.kiev.ua");
        $client->setDeveloperKey("AIzaSyD7QiC2AO4PUtiMRN9i5SfZOAhZLvSnGzw");

        return $client;
    }
}
