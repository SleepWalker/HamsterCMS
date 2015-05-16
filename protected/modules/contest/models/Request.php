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
 * @property string $meta
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
    public $meta = [];

    const STATUS_NEW = 1;
    const STATUS_DECLINED = 2;
    const STATUS_ACCEPTED = 3;
    const STATUS_WAIT_CONFIRM = 4;
    const STATUS_CONFIRMED = 5;

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

    public function getConfirmationKey()
    {
        // TODO: move to MD
        return md5($this->primaryKey.$this->date_created);
    }

    public function isValidConfirmationKey($key)
    {
        return $key === $this->getConfirmationKey();
    }

    public function isConfirmed()
    {
        return $this->status == self::STATUS_CONFIRMED;
    }

    /**
     * @throws \Excption IF the key is invalid
     */
    public function confirm($key, \contest\models\view\ConfirmForm $confirmModel)
    {
        if (!$this->isValidConfirmationKey($key)) {
            throw new \Exception('Invalid confirmation key');
        }

        // TODO: should we call validate on confirm model?

        $this->status = self::STATUS_CONFIRMED;

        $this->meta['confirmation'] = $confirmModel->attributes;
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
            self::STATUS_WAIT_CONFIRM => 'Ожидает подтвердждения',
            self::STATUS_CONFIRMED => 'Подтверждена',
        ];
    }

    protected function beforeSave()
    {
        if (parent::beforeSave()) {
            $this->meta = \CJSON::encode($this->meta);

            return true;
        }

        return false;
    }

    protected function afterSave()
    {
        parent::beforeSave();
        $this->afterFind();
    }

    protected function afterFind()
    {
        parent::afterFind();
        if (is_string($this->meta)) {
            $this->meta = \CJSON::decode($this->meta);
        }
    }

    /**
     * TODO: need to be refactored according to DDD architecture
     * @throws  Exception IF it is a new model without pk
     * @return  \contest\models\view\Request
     */
    public function getViewModel()
    {
        if ($this->isNewRecord) {
            throw new \Exception('The model should be created from persisted entity');
        }

        $request = new \contest\models\view\Request();

        $request->attributes = $this->attributes;
        $request->id = $this->id;
        foreach ($this->musicians as $musician) {
            $attributes = $musician->attributes;
            if (!empty($attributes['birthdate'])) {
                $attributes['birthdate'] = date('d.m.Y', strtotime($attributes['birthdate']));
            }

            $request->addMusician($attributes);
        }

        foreach ($this->compositions as $composition) {
            $attributes = $composition->attributes;

            $request->addComposition($attributes);
        }

        return $request;
    }

    /**
     * @return  \contest\models\view\ConfirmForm
     */
    public function getConfirmViewModel()
    {
        $model = new \contest\models\view\ConfirmForm();

        if (isset($this->meta['confirmation'])) {
            $model->attributes = $this->meta['confirmation'];
        }

        return $model;
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
