<?php

namespace sectionvideo\repositories;

use \sectionvideo\models\VideoRating;

class RatingRepository extends \sectionvideo\components\Repository
{
    public function getModel()
    {
        return VideoRating::model();
    }

    public function addVideoLike($videoId)
    {
        $this->assertId($videoId);

        $rating = new VideoRating();
    }

    public function getVideoRating($videoId)
    {
        $this->assertId($videoId);

        $rating = $this->getModel()->findAllByAttributes(['source_id' => $videoId]);

        return $rating && $rating->value > 0 ? $rating->value : 0;
    }

    private function assertId($videoId)
    {
        if (!is_numeric($videoId)) {
            throw new \InvalidArgumentException('Wrong video id provided: ' . $videoId);
        }
    }
}
