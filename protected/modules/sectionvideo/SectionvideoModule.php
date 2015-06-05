<?php
/**
 * Blog module main file
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    sectionVideo.SectionVideoModule
 * @copyright  Copyright &copy; 2013 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

class SectionvideoModule extends \CWebModule
{
    public function init()
    {
        $this->setComponents([
            'externalVideo' => [
                'class' => '\sectionvideo\components\ExternalVideo',
            ],
        ]);
    }
}
