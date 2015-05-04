<?php
/**
 * The model for supporting registering for contest
 *
 * The followings are the available columns in table 'contest_request':
 * @property string $id
 * @property string $name
 * @property string $type
 * @property string $format
 * @property string $demos
 * @property integer $status
 * @property string $date_created
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @copyright  Copyright &copy; 2015 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

namespace contest\models;

class Request extends \CActiveRecord
{
    public $type = 'solo';

    const STATUS_NEW = 1;
    const STATUS_DECLINED = 2;
    const STATUS_ACCEPTED = 3;

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('type', 'required'),
            array('name', 'length', 'max' => 128),
            array('type', 'in', 'range' => array('solo', 'group')),
            array('format', 'numerical', 'integerOnly' => true),
            array('demos', 'safe'),
        );
    }

    public function relations()
    {
        return [
            'musicians' => [self::HAS_MANY, '\contest\models\Musician', 'request_id'],
            'compositions' => [self::HAS_MANY, '\contest\models\Composition', 'request_id'],
        ];
    }

    public function getFormatLabel()
    {
        if ($this->type == 'group') {
            return 'Группа';
        }

        $map = [
            view\Request::FORMAT_SOLO => 'Сольное исполнение (без сопровождения)',
            view\Request::FORMAT_MINUS => 'Сольное исполнение под минус',
            view\Request::FORMAT_CONCERTMASTER => 'Сольное исполнение с концертмейстером',
        ];
        return !empty($this->format) ? $map[$this->format] : '';
    }

    public function getStatusLabel()
    {
        $map = $this->getStatusesList();
        return !empty($this->status) ? $map[$this->status] : 'Undefined';
    }

    public function getStatusesList()
    {
        return [
            self::STATUS_NEW => 'Новая заявка',
            self::STATUS_DECLINED => 'Отклонена',
            self::STATUS_ACCEPTED => 'Принята',
        ];
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{contest_request}}';
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria = new \CDbCriteria();

        // $criteria->compare('title', $this->title, true);
        // $criteria->compare('content', $this->content, true);

        return new \CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }
}
