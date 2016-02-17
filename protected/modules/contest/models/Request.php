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
 */

namespace contest\models;

use contest\models\view\ApplyForm;
use contest\models\view\ConfirmForm;

class Request extends \CActiveRecord
{
    public $type = self::TYPE_SOLO;
    public $meta = [];

    // TODO: remove after migration
    public $contact_name;
    public $contact_email;
    public $contact_phone;
    public $age_category;

    const STATUS_NEW = 1;
    const STATUS_DECLINED = 2;
    const STATUS_ACCEPTED = 3;
    const STATUS_WAIT_CONFIRM = 4;
    const STATUS_CONFIRMED = 5;

    const FORMAT_SOLO = 1;
    const FORMAT_MINUS = 2;
    const FORMAT_CONCERTMASTER = 3;
    const FORMAT_INSTRUMENTAL_SOLO = 4;
    const FORMAT_VOCAL_SOLO = 5;
    const FORMAT_GROUP = 6;

    const AGE_CATEGORY_10 = 1;
    const AGE_CATEGORY_11_14 = 2;
    const AGE_CATEGORY_15_17 = 3;
    const AGE_CATEGORY_18 = 4;

    const TYPE_SOLO = 'solo';
    const TYPE_GROUP = 'group';

    const SCENARIO_SOLO = 'solo';
    const SCENARIO_GROUP = 'group';

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['contact_name, contact_email, contact_phone', 'required'],
            ['age_category', 'required',
                'message' => 'Пожалуйста выберите возраст'],
            ['format', 'required',
                'message' => 'Пожалуйста выберите формат'],
            ['name, contact_name', 'length', 'max' => 128],

            ['age_category, format', 'numerical', 'integerOnly' => true],
            ['format', 'in',
                'range' => array_keys($this->getFormatsList())],
            ['age_category', 'in',
                'range' => array_keys($this->getAgeCategoriesList())],

            ['demos', 'safe'],

            ['contact_phone', 'length', 'max' => 25],
            ['contact_phone', 'match',
                'pattern' => '/\+38 \(\d{3}\) \d{3}\-\d{2}\-\d{2}/',
                'message' => 'Введите телефон в формате +38 (00) 000-00-00',
            ],

            ['contact_email', 'email'],

            ['name', 'required', 'except' => 'solo'],
        ];
    }

    public function relations()
    {
        return [
            'musicians' => [self::HAS_MANY, Musician::class, 'request_id'],
            'compositions' => [self::HAS_MANY, Composition::class, 'request_id'],
        ];
    }

    /**
     * @return string group name or first musician name
     */
    public function getMainName()
    {
        return !empty($this->name) ? $this->name : $this->musicians[0]->getFullName();
    }

    public function getConfirmationKey()
    {
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

    public function isAccepted()
    {
        return $this->status != self::STATUS_NEW && $this->status != self::STATUS_DECLINED;
    }

    /**
     * @throws \DomainException IF the key is invalid
     */
    public function confirm($key, ConfirmForm $confirmModel)
    {
        if (!$confirmModel->validate()) {
            throw new \InvalidArgumentException('Invalid confirmation form data');
        }

        if (!$this->isValidConfirmationKey($key)) {
            throw new \InvalidArgumentException('Invalid confirmation key');
        }

        $this->status = self::STATUS_CONFIRMED;

        $this->meta['confirmation'] = $confirmModel->attributes;
    }

    public function getFormatLabel()
    {
        $format = $this->format;

        if ($this->isGroup()) {
            $format = self::FORMAT_GROUP;
        }

        $map = [
            self::FORMAT_SOLO => 'Соло',
            self::FORMAT_MINUS => 'Минус',
            self::FORMAT_CONCERTMASTER => 'Концертмейстер',
            self::FORMAT_INSTRUMENTAL_SOLO => 'Инструментальное соло',
            self::FORMAT_VOCAL_SOLO => 'Вокальное соло',
            self::FORMAT_GROUP => 'Вокально-инстр. ансамбль',
        ];

        return !empty($this->format) ? $map[$format] : '';
    }

    public function getStatusLabel()
    {
        $map = $this->getStatusesList();
        return !empty($this->status) ? $map[$this->status] : 'Undefined';
    }

    public function getNominationLabel()
    {
        $isGroup = $this->isGroup();
        $hasVocal = count(array_filter($this->musicians, function ($musician) {
            return preg_match('/вокал/iu', $musician->instrument);
        })) > 0;

        switch ((int)$isGroup.(int)$hasVocal) {
            case '00':
                $nomination = 'Инструментальное соло';
                break;
            case '10':
                $nomination = 'Инстр. ансамбль';
                break;
            case '01':
                $nomination = 'Вокальное соло';
                break;
            case '11':
                $nomination = 'Вокально-инстр. ансамбль';
                break;
        }

        return $nomination;
    }

    public function getAgeCategoryLabel()
    {
        $ageMap = [
            10 => 'до 10 лет',
            14 => '11-14 лет',
            17 => '15-17 лет',
            100 => '18 лет и старше',
        ];
        $ageMap = array_reverse($ageMap, true);

        if ($this->isGroup()) {
            $ages = array_map(function ($musician) {
                return $musician->age;
            }, $this->musicians);
            $age = min(self::median($ages), self::average($ages));
        } else {
            $age = $this->musicians[0]->age;
        }

        foreach ($ageMap as $ageThreshold => $label) {
            if ($age <= $ageThreshold) {
                $ageCategory = $label;
            }
        }

        return $ageCategory;
    }

    private static function median($arr)
    {
        sort($arr);
        $count = count($arr); //total numbers in array
        $middleval = floor(($count-1)/2); // find the middle value, or the lowest middle value
        if ($count % 2) { // odd number, middle is the median
            $median = $arr[$middleval];
        } else { // even number, calculate avg of 2 medians
            $low = $arr[$middleval];
            $high = $arr[$middleval+1];
            $median = (($low+$high)/2);
        }
        return $median;
    }

    private static function average($arr)
    {
        $total = 0;
        $count = count($arr); //total numbers in array
        foreach ($arr as $value) {
            $total +=  + $value; // total value of array numbers
        }
        $average = ($total/$count); // get average value
        return $average;
    }

    public function isGroup()
    {
        return $this->type == self::TYPE_GROUP || $this->format === self::FORMAT_GROUP;
    }

    /**
     * @return array list for radio button/dropdown list
     */
    public function getFormatsList()
    {
        return [
            // self::FORMAT_SOLO => 'Сольное исполнение (без сопровождения)',
            // self::FORMAT_MINUS => 'Сольное исполнение под минус',
            // self::FORMAT_CONCERTMASTER => 'Сольное исполнение с концертмейстером',
            self::FORMAT_INSTRUMENTAL_SOLO => 'Инструментальное соло',
            self::FORMAT_VOCAL_SOLO => 'Вокальное соло',
            self::FORMAT_GROUP => 'Вокально-инстр. ансамбль',
        ];
    }

    public function getAgeCategoriesList()
    {
        return [
            self::AGE_CATEGORY_10 => 'до 10 лет',
            self::AGE_CATEGORY_11_14 => '11-14 лет',
            self::AGE_CATEGORY_15_17 => '15-17 лет',
            self::AGE_CATEGORY_18 => '18 лет и старше',
        ];
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

    public function attributeLabels()
    {
        return [
            'name' => 'Название группы',
            'demos' => 'Демо и дополнительная информация',
            'type' => 'Номинация',
            'format' => 'Формат номера',
            'compositions' => 'Исполняемые композиции',
            'musicians' => 'Исполнитель(-ли)',
            'contact_name' => 'Ваше имя',
            'contact_email' => 'Email',
            'contact_phone' => 'Телефон',
        ];
    }

    /**
     * @throws  Exception IF it is a new model without pk
     *
     * @return  ApplyForm
     */
    public function getApplyForm()
    {
        if ($this->isNewRecord) {
            throw new \Exception('The model should be created from persisted entity');
        }

        return new ApplyForm($this, $this->musicians, $this->compositions);
    }

    /**
     * @return  ConfirmForm
     */
    public function getConfirmForm()
    {
        $model = new ConfirmForm();

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

        return new \CActiveDataProvider($this, [
            'criteria' => $criteria,
        ]);
    }
}
