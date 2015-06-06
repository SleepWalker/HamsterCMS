<?php

namespace sectionvideo\repositories;

use \sectionvideo\models\VideoRating;

class RatingRepository extends \sectionvideo\components\Repository
{
    public function getModel()
    {
        return VideoRating::model();
    }

    /**
     * @param integer $videoId
     * @throws \DomainException if wrong $videoId or invalid model
     */
    public function addLike($videoId)
    {
        $this->assertId($videoId);

        $rating = new VideoRating();
        $rating->source_id = $videoId;
        $rating->ip = \Yii::app()->request->getUserHostAddress();

        $this->save($rating);
    }

    /**
     * @param integer $videoId
     * @throws \DomainException if wrong $videoId
     */
    public function getRating($videoId)
    {
        $this->assertId($videoId);

        return $this->getModel()->countByAttributes(['source_id' => $videoId]);
    }
}
