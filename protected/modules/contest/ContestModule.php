<?php
/**
 * Contest module
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    contest
 * @copyright  Copyright &copy; 2015 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

class ContestModule extends CWebModule
{
    public function getAdminEmail()
    {
        return isset($this->params['adminEmail']) ? $this->params['adminEmail'] : Yii::app()->params['adminEmail'];
    }
}
