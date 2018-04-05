<?php
namespace contest\models;

/**
 * The followings are the available columns in table:
 * @property string $id
 * @property string $title
 * @property string $type
 * @property string $dateCreated
 */
class Contest extends \CActiveRecord
{
    const TYPE_CONTEST = 'contest';
    const TYPE_FESTIVAL = 'festival';

    /**
     * @return string the associated database table name
     */
    public function tableName(): string
    {
        return '{{contest_contest}}';
    }

    public function canApply(): bool
    {
        $now = time();
        $start = strtotime($this->applicationStartDate);
        $end = strtotime($this->applicationEndDate);

        if ($end && $end < $now) {
            // allow admins to apply at any time
            return \Yii::app()->user->checkAccess('admin');
        }

        return $start && $start < $now;
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules(): array
    {
        return [
            ['title', 'required'],
            ['title', 'length', 'max' => 128],
            ['type', 'in', 'range' => $this->getTypes()],
            ['applicationStartDate, applicationEndDate', 'date',
                'format' => ['yyyy-MM-dd hh:mm', 'yyyy-MM-dd hh:mm:ss']
            ],
        ];
    }

    protected function beforeSave()
    {
        if (empty($this->applicationStartDate)) {
            $this->applicationStartDate = new \CDbExpression('NULL');
        }

        if (empty($this->applicationEndDate)) {
            $this->applicationEndDate = new \CDbExpression('NULL');
        }

        return parent::beforeSave();
    }

    public function afterSave()
    {
        if ($this->applicationStartDate instanceof \CDbExpression) {
            $this->applicationStartDate = null;
        }

        if ($this->applicationEndDate instanceof \CDbExpression) {
            $this->applicationEndDate = null;
        }
    }

    public function getTypes(): array
    {
        return array_keys($this->getTypesList());
    }

    public function getTypesList(): array
    {
        return [
            self::TYPE_CONTEST => 'Конкурс',
            self::TYPE_FESTIVAL => 'Фестиваль',
        ];
    }

    public function isContest(): bool
    {
        return $this->type === self::TYPE_CONTEST;
    }

    public function getFieldTypes(): array
    {
        return [
            'title' => 'text',
            'type' => ['dropdownlist', 'items' => $this->getTypesList()],
            'applicationStartDate' => 'datetime',
            'applicationEndDate' => 'datetime',
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'title' => 'Название конкурса',
            'type' => 'Тип',
            'dateCreated' => 'Дата создания',
            'applicationStartDate' => 'Дата начала приема заявок на участие',
            'applicationEndDate' => 'Дата окончания приема заявок на участие',
        ];
    }

    public function relations(): array
    {
        return [
        ];
    }

    public function search(): \CActiveDataProvider
    {
        return new \CActiveDataProvider($this, [
            'criteria' => new \CDbCriteria(),
        ]);
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
