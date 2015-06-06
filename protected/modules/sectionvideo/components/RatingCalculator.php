<?php

namespace sectionvideo\components;

class RatingCalculator extends \CApplicationComponent
{
    public function refreshRatingCounters()
    {
        $videos = \Yii::app()->getModule('sectionvideo')->videoRepository->all();

        foreach ($videos as $video) {
            $video->likes = $this->calculateRating($video->primaryKey, $video->video_url);
            \Yii::app()->getModule('sectionvideo')->videoRepository->save($video);
        }
    }

    /**
     * @return integer rating
     */
    public function calculateRating($localId, $externalId)
    {
        $externalRating = $this->getExternalRating($externalId);

        $localRating = $this->getLocalRating($localId);

        return $externalRating + $localRating;
    }

    /**
     * @param  integer $videoId
     * @return integer           rating
     */
    public function getLocalRating($videoId)
    {
        return \Yii::app()->getModule('sectionvideo')->ratingRepository->getVideoRating($videoId);
    }

    /**
     * @param  string  $extVideoId external video url or id
     * @return integer             rating
     */
    public function getExternalRating($extVideoId)
    {
        return \Yii::app()->getModule('sectionvideo')
            ->externalVideo
            ->create($extVideoId)
            ->getLikes()
            ;
    }
}
