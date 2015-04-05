<?php
/**
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @copyright  Copyright &copy; 2015 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

namespace contest\models\view;

class Composition extends \CFormModel
{
    public $author;
    public $title;
    public $duration;

    public function rules()
    {
        return array(
            array('author, title, duration', 'required', 'message' => false),

            array('duration', 'numerical', 'integerOnly' => true, 'max' => 15, 'message' => 'Время должно быть числом'),
        );
    }

    public function attributeLabels()
    {
        return array(
            'author' => 'Автор',
            'title' => 'Название',
            'duration' => 'Время, мин',
        );
    }
}
