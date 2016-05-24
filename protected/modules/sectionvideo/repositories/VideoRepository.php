<?php

namespace sectionvideo\repositories;

use \sectionvideo\models\Video;

class VideoRepository extends \sectionvideo\components\Repository
{
    public function getModel()
    {
        return Video::model()->published();
    }
}
