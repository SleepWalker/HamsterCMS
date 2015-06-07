<?php

namespace sectionvideo\components;

class YoutubeVideo
{
    private $videoId;
    private $client;

    public function __construct($videoId, \Google_Client $client)
    {
        $this->videoId = $this->parseId($videoId);
        $this->client = $client;
    }

    public function getVideoId()
    {
        return $this->videoId;
    }

    /**
     * @return integer
     */
    public function getLikes()
    {
        return $this->getData()['statistics']['likeCount'];
    }

    /**
     * @return integer
     */
    public function getViews()
    {
        return $this->getData()['statistics']['viewCount'];
    }

    /**
     * @return array
     */
    public function getThumbnails()
    {
        return $this->getData()['snippet']['thumbnails'];
    }

    /**
     * @param  string $size размер изображения. Доступные размеры: full, list, grid
     * @return string
     */
    public function getThumbnail($size = null)
    {
        return (new \sectionvideo\components\VideoImage($this->getVideoId()))->get($size);
    }

    /**
     * @return string
     */
    public function getPlayerCode()
    {
        // TODO: делигировать на другой класс
        // html5=1 - исправляет баг с нерабочим видео в firefox в случае если оно вставлялось на страницу после ее загрузки (к примеру через ajax)
        return '<iframe width="100%" height="100%" src="//www.youtube.com/embed/' . $this->videoId . '?html5=1" frameborder="0" allowfullscreen></iframe>';
    }

    public function parseId($str)
    {
        $id = $str;
        if (strpos($str, 'v=')) {
            parse_str(parse_url($str, PHP_URL_QUERY), $params);
            $id = $params['v'];
        }

        return $id;
    }

    /**
     * @throws Exception IF no data
     * @return array video data from google api
     */
    private function getData()
    {
        $youtube = new \Google_Service_YouTube($this->client);

        $listResponse = $youtube->videos->listVideos("statistics,snippet", ['id' => $this->videoId]);

        if (empty($listResponse)) {
            throw new \Exception(get_class($this) . "::getData(): No data for specified video, probably wrong id");
        }

        return $listResponse[0];
        /*
        'title' => $video['snippet']['title'],
        'description' => $video['snippet']['description'],
        'title' => $video['snippet']['title'],
        'thumbnails' => $video['snippet']['thumbnails'],
        'tags' => $video['snippet']['tags'],
        'viewCount' => $video['statistics']['viewCount'],
        'likeCount' => $video['statistics']['likeCount'],
        'dislikeCount' => $video['statistics']['dislikeCount'],
        'favoriteCount' => $video['statistics']['favoriteCount'],
        'commentCount' => $video['statistics']['commentCount'],
        'embedHtml' => $video['player']['embedHtml'],
         */
    }
}
