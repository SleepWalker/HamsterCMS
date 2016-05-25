<?php
/**
 * Section video module main file
 */

class SectionvideoModule extends \CWebModule
{
    public $controllerNamespace = '\sectionvideo\controllers';

    public function init()
    {
        $this->setComponents([
            'externalVideo' => [
                'class' => '\sectionvideo\components\ExternalVideo',
            ],
            'ratingCalculator' => [
                'class' => '\sectionvideo\components\RatingCalculator',
            ],
            'videoRepository' => [
                'class' => '\sectionvideo\repositories\VideoRepository',
            ],
            'ratingRepository' => [
                'class' => '\sectionvideo\repositories\RatingRepository',
            ],
        ]);
    }
}
