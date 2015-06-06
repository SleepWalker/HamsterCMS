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
     * @param integer $videoId
     * @throws \InvalidArgumentException if wrong $videoId
     * @throws \DomainException no video or like can not be added
     * @throws \CDbException if can not save
     */
    public function addLike($videoId)
    {
        $transaction = \Yii::app()->db->beginTransaction();
        try {
            $video = \Yii::app()->getModule('sectionvideo')->videoRepository->get($videoId);

            if (!$video) {
                throw new \DomainException('The video does not exists');
            }

            \Yii::app()->getModule('sectionvideo')->ratingRepository->addLike($videoId);

            $video->likes++;

            \Yii::app()->getModule('sectionvideo')->videoRepository->save($video);

            $transaction->commit();
            return $video->likes;
        } catch (\Exception $e) {
            $transaction->rollBack();

            \Yii::log('Error adding vote: ' . $e->getMessage(), \CLogger::LEVEL_ERROR);

            throw new \DomainException('Error saving data', 0, $e);
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
        return \Yii::app()->getModule('sectionvideo')->ratingRepository->getRating($videoId);
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
