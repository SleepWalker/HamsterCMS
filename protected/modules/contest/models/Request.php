<?php
namespace contest\models;

use KoKoKo\assert\Assert;

use contest\models\view\ApplyForm;

/**
 * The model for supporting registering for contest
 *
 * The followings are the available columns in table 'contest_request':
 * @property string $id
 * @property string $contest_id
 * @property string $name
 * @property string $contact_name
 * @property string $contact_email
 * @property string $contact_phone
 * @property string $type
 * @property string $format
 * @property string $age_category
 * @property string $demos
 * @property integer $status
 * @property string $meta
 * @property string $date_created
 */
class Request extends \CActiveRecord
{
    public $meta = [];
    public $status = self::STATUS_NEW;

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

    public $type; // DEPRECATED
    const TYPE_SOLO = 'solo'; // DEPRECATED
    const TYPE_GROUP = 'group'; // DEPRECATED

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
                'message' => 'Будь ласка, оберіть вікову категорію'],
            ['format', 'required',
                'message' => 'Будь ласка оберіть формат номеру'],
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
                'message' => 'Введите телефон в формате +38 (000) 000-00-00',
            ],

            ['contact_email', 'email'],

            ['name', 'required', 'on' => self::SCENARIO_GROUP],
        ];
    }

    public function relations()
    {
        return [
            'contest' => [self::BELONGS_TO, Contest::class, 'contest_id'],
            'musicians' => [self::HAS_MANY, Musician::class, 'request_id'],
            'compositions' => [self::HAS_MANY, Composition::class, 'request_id'],
        ];
    }

    /**
     * @return string group name or first musician name for e.g. export tables
     */
    public function getMainName() : string
    {
        if (!empty($this->name)) {
            return $this->name;
        } elseif (!empty($this->contact_name)) {
            return $this->contact_name;
        } else {
            return $this->musicians[0]->getFullName();
        }
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
    public function confirm($key)
    {
        if (!$this->isValidConfirmationKey($key)) {
            throw new \InvalidArgumentException('Invalid confirmation key');
        }

        $this->status = self::STATUS_CONFIRMED;
    }

    public function getFormatLabel(): string
    {
        $format = $this->format;
        $formatMap = $this->getFormatsList();

        if ($this->isGroup()) {
            $format = self::FORMAT_GROUP;
        }

        return $formatMap[$format] ?? 'Нет данных';
    }

    public function getStatusLabel(): string
    {
        $map = $this->getStatusesList();

        return $map[$this->status] ?? 'Нет данных';
    }

    public function getAgeCategoryLabel(): string
    {
        $ageMap = $this->getAgeCategoriesList();

        return $ageMap[$this->age_category] ?? 'Нет данных';
    }

    public function isGroup(): bool
    {
        return $this->type === self::TYPE_GROUP || (int)$this->format === self::FORMAT_GROUP;
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
            self::FORMAT_INSTRUMENTAL_SOLO => 'Інструментальне соло',
            self::FORMAT_VOCAL_SOLO => 'Вокальне соло',
            self::FORMAT_GROUP => 'Вокально-інстр. ансамбль',
        ];
    }

    public function getAgeCategoriesList()
    {
        return [
            self::AGE_CATEGORY_10 => 'до 10 років',
            self::AGE_CATEGORY_11_14 => '11-14 років',
            self::AGE_CATEGORY_15_17 => '15-17 років',
            self::AGE_CATEGORY_18 => '18 років та більше',
        ];
    }

    public static function getStatusesList()
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
        Assert::assert((int) $this->contest_id, 'contest_id')->greater(1);
        Assert::assert((int) $this->status, 'status')->inArray(array_keys($this->getStatusesList()));

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
            'name' => 'Назва гурту',
            'demos' => 'Демо та додаткова інформація',
            'type' => 'Номінація',
            'age_category' => 'Вікова категорія',
            'format' => 'Формат номеру',
            'compositions' => 'Виконувані композиції',
            'musicians' => 'Виконавець(-ці)',
            'contact_name' => 'Ваше ім\'я',
            'contact_email' => 'Email',
            'contact_phone' => 'Телефон',
        ];
    }

    /**
     * @throws  DomainException IF it is a new model without pk
     *
     * @return  ApplyForm
     */
    public function getApplyForm()
    {
        if ($this->isNewRecord) {
            throw new \DomainException('The model should be created from persisted entity');
        }

        return new ApplyForm($this, $this->musicians, $this->compositions);
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

        $criteria->compare('contest_id', $this->contest_id);
        $criteria->compare('status', $this->status);
        // $criteria->compare('title', $this->title, true);
        // $criteria->compare('content', $this->content, true);

        return new \CActiveDataProvider($this, [
            'criteria' => $criteria,
        ]);
    }
}
