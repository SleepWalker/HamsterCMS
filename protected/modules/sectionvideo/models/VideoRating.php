<?php

namespace sectionvideo\models;

class VideoRating extends \application\models\Rating
{
    public function tableName()
    {
        return '{{section_video_rating}}';
    }
}
